<?php

require_once plugin_dir_path(__FILE__) . 'laravel-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'models/ScGooMaker.php';
require_once plugin_dir_path(__FILE__) . 'models/ScGooModel.php';

add_action('admin_menu', function () {
    add_menu_page(
        'model_contents管理',
        'model_contents管理',
        'manage_options',
        'laravel-model-contents',
        'laravel_model_contents_page',
        'dashicons-database',
        25
    );
});

function laravel_model_contents_page() {
    global $laravel_db;

    // 検索条件
    $selected_maker = isset($_GET['maker_name_id']) ? intval($_GET['maker_name_id']) : '';
    $selected_model = isset($_GET['model_name_id']) ? intval($_GET['model_name_id']) : '';

    // フィルター取得
    $makers = ScGooMaker::all($laravel_db);
    $models = $selected_maker ? ScGooModel::getByMakerId($laravel_db, $selected_maker) : [];

    // 検索フォーム
    echo '<div class="wrap">';
    echo '<h2>検索フィルター</h2>';
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="laravel-model-contents">';

    echo '<p><label>メーカー名: ';
    echo '<select name="maker_name_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($makers as $maker) {
        $selected = $maker->id == $selected_maker ? 'selected' : '';
        echo "<option value=\"{$maker->id}\" {$selected}>{$maker->maker_name}</option>";
    }
    echo '</select></label></p>';

    echo '<p><label>モデル名: ';
    echo '<select name="model_name_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($models as $model) {
        $selected = $model->id == $selected_model ? 'selected' : '';
        echo "<option value=\"{$model->id}\" {$selected}>{$model->model_name}</option>";
    }
    echo '</select></label></p>';

    echo '<noscript><p><button type="submit" class="button">検索</button></p></noscript>';
    echo '</form>';
    echo '</div>';

    // 検索条件によるWHERE句
    $where = [];
    $params = [];
    if ($selected_maker) {
        $where[] = 'maker_name_id = %d';
        $params[] = $selected_maker;
    }
    if ($selected_model) {
        $where[] = 'model_name_id = %d';
        $params[] = $selected_model;
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // ページネーション
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($current_page - 1) * $per_page;

    // 件数取得
    $total_items = $laravel_db->get_var($laravel_db->prepare(
        "SELECT COUNT(*) FROM model_contents {$where_sql}",
        ...$params
    ));
    $total_pages = ceil($total_items / $per_page);

    // データ取得
    $query_params = array_merge($params, [$per_page, $offset]);
    $results = $laravel_db->get_results($laravel_db->prepare(
        "SELECT * FROM model_contents {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
        ...$query_params
    ));

    // 削除処理
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $laravel_db->delete('model_contents', ['id' => $delete_id]);
        echo '<div class="notice notice-warning"><p>ID ' . $delete_id . ' を削除しました。</p></div>';
    }

    // 編集データ取得
    $editing = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $editing = $laravel_db->get_row($laravel_db->prepare("SELECT * FROM model_contents WHERE id = %d", $edit_id));
    }

    // 保存処理
    if (isset($_POST['save_model'])) {
        $maker_name_id = intval($_POST['maker_name_id']);
        $model_name_id = intval($_POST['model_name_id']);
        $model_text_content = sanitize_text_field($_POST['model_text_content']);
        $now = current_time('mysql');

        if (!empty($_POST['editing_id'])) {
            $laravel_db->update('model_contents', [
                'maker_name_id' => $maker_name_id,
                'model_name_id' => $model_name_id,
                'model_text_content' => $model_text_content,
                'updated_at' => $now
            ], ['id' => intval($_POST['editing_id'])]);
            echo '<div class="notice notice-success"><p>更新しました！</p></div>';
        } else {
            $laravel_db->insert('model_contents', [
                'maker_name_id' => $maker_name_id,
                'model_name_id' => $model_name_id,
                'model_text_content' => $model_text_content,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            echo '<div class="notice notice-success"><p>新規追加しました！</p></div>';
        }
    }

    // 一覧表示
    echo '<div class="wrap"><h1>Laravelモデル一覧</h1>';
    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>ID</th><th>メーカーID</th><th>モデルID</th><th>内容</th><th>更新日</th><th>操作</th></tr></thead>';
    echo '<tbody>';
    foreach ($results as $row) {
        $edit_link = add_query_arg(['edit_id' => $row->id], admin_url('admin.php?page=laravel-model-contents'));
        $delete_link = add_query_arg(['delete_id' => $row->id], admin_url('admin.php?page=laravel-model-contents'));
        echo '<tr>';
        echo "<td>{$row->id}</td>";
        echo "<td>{$row->maker_name_id}</td>";
        echo "<td>{$row->model_name_id}</td>";
        echo "<td>" . esc_html($row->model_text_content) . "</td>";
        echo "<td>{$row->updated_at}</td>";
        echo "<td>
                <a href='{$edit_link}' class='button'>編集</a>
                <a href='{$delete_link}' class='button button-danger' onclick='return confirm(\"本当に削除しますか？\")'>削除</a>
              </td>";
        echo '</tr>';
    }
    echo '</tbody></table>';

    // ページネーション
    $base_url = admin_url('admin.php?page=laravel-model-contents');
    $base_url = add_query_arg(array_filter([
        'maker_name_id' => $selected_maker,
        'model_name_id' => $selected_model
    ]), $base_url);

    echo '<div style="margin-top: 20px;">';
    if ($current_page > 1) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '">&laquo; 前へ</a> ';
    }
    if ($current_page < $total_pages) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '">次へ &raquo;</a>';
    }
    echo "<p>ページ {$current_page} / {$total_pages}</p>";
    echo '</div>';

    // 編集 or 新規フォーム（左下固定）
    echo '<div id="fixed-form" style="position: fixed; bottom: 20px; width: 300px; background: #fff; border: 1px solid #ccc; padding: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 9999;">';
    echo '<h2 style="margin-top: 0;">' . ($editing ? '編集' : '新規追加') . '</h2>';
    echo '<form method="POST">';
    if ($editing) {
        echo '<input type="hidden" name="editing_id" value="' . intval($editing->id) . '">';
    }
    echo '<p><label>メーカーID:<br><input type="number" name="maker_name_id" required value="' . ($editing ? esc_attr($editing->maker_name_id) : '') . '" style="width:100%;"></label></p>';
    echo '<p><label>モデルID:<br><input type="number" name="model_name_id" required value="' . ($editing ? esc_attr($editing->model_name_id) : '') . '" style="width:100%;"></label></p>';
    echo '<p><label>内容:<br><textarea name="model_text_content" rows="3" style="width:100%;">' . ($editing ? esc_html($editing->model_text_content) : '') . '</textarea></label></p>';
    echo '<p><button type="submit" name="save_model" class="button button-primary" style="width:100%;">保存</button></p>';
    echo '</form>';
    echo '</div>';
}
