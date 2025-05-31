<?php

require_once plugin_dir_path(__FILE__) . 'laravel-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'models/WP_ScGooMaker.php';
require_once plugin_dir_path(__FILE__) . 'models/WP_ScGooModel.php';

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_laravel-model-management') return;

    wp_enqueue_style(
        'custom-laravel-access-css',
        plugin_dir_url(__FILE__) . 'css/plugins.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'css/plugins.css')
    );

    wp_enqueue_script(
        'custom-laravel-access-js',
        plugin_dir_url(__FILE__) . 'javascript/form.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'javascript/form.js'),
        true
    );
});

add_action('admin_menu', function () {
    add_menu_page(
        'sc_goo_model管理',
        'sc_goo_model管理',
        'manage_options',
        'laravel-model-management',
        'laravel_model_management_page',
        'dashicons-car',
        27
    );
});

function laravel_model_management_page()
{
    global $laravel_db;

    // 削除処理
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $laravel_db->delete('sc_goo_model', ['id' => $delete_id]);
        echo '<div class="notice notice-warning"><p>ID ' . $delete_id . ' を削除しました。</p></div>';
    }

    // 編集データ取得
    $editing = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $editing = $laravel_db->get_row($laravel_db->prepare("SELECT * FROM sc_goo_model WHERE id = %d", $edit_id));
    }

    // 保存処理
    if (isset($_POST['save_model'])) {
        $maker_id = intval($_POST['maker_name_id']);
        $model_name = sanitize_text_field($_POST['model_name']);
        $now = current_time('mysql');

        if (!empty($_POST['editing_id'])) {
            $laravel_db->update('sc_goo_model', [
                'maker_name_id' => $maker_id,
                'model_name' => $model_name,
                'updated_at' => $now,
            ], ['id' => intval($_POST['editing_id'])]);
            echo '<div class="notice notice-success"><p>更新しました！</p></div>';
        } else {
            $laravel_db->insert('sc_goo_model', [
                'maker_name_id' => $maker_id,
                'model_name' => $model_name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            echo '<div class="notice notice-success"><p>新規追加しました！</p></div>';
        }
    }

    // 検索条件
    $selected_maker = isset($_GET['maker_name_id']) ? intval($_GET['maker_name_id']) : '';
    $selected_model = isset($_GET['model_id']) ? intval($_GET['model_id']) : '';
    $makers = WP_ScGooMaker::all($laravel_db);
    $models = $selected_maker ? WP_ScGooModel::getByMakerId($laravel_db, $selected_maker) : [];

    // WHERE句
    $where = [];
    $params = [];
    if ($selected_maker) {
        $where[] = 'maker_name_id = %d';
        $params[] = $selected_maker;
    }
    if ($selected_model) {
        $where[] = 'id = %d';
        $params[] = $selected_model;
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // ページネーション
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 10;
    $offset = ($current_page - 1) * $per_page;

    $total_items = $laravel_db->get_var(
        $laravel_db->prepare("SELECT COUNT(*) FROM sc_goo_model {$where_sql}", ...$params)
    );
    $total_pages = ceil($total_items / $per_page);

    $query_params = array_merge($params, [$per_page, $offset]);
    $results = $laravel_db->get_results(
        $laravel_db->prepare("SELECT * FROM sc_goo_model {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d", ...$query_params)
    );

    // 検索フォーム
    echo '<div class="wrap">';
    echo '<h1>モデル一覧</h1>';
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="laravel-model-management">';
    echo '<p><label>メーカー名: <select name="maker_name_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($makers as $maker) {
        $selected = $maker->id == $selected_maker ? 'selected' : '';
        echo "<option value='{$maker->id}' {$selected}>{$maker->maker_name}</option>";
    }
    echo '</select></label></p>';

    echo '<p><label>モデル名: <select name="model_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($models as $model) {
        $selected = $model->id == $selected_model ? 'selected' : '';
        echo "<option value='{$model->id}' {$selected}>{$model->model_name}</option>";
    }
    echo '</select></label></p>';
    echo '</form>';

    // テーブル表示
    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>ID</th><th>メーカーID</th><th>モデル名</th><th>作成日</th><th>更新日</th><th>操作</th></tr></thead><tbody>';
    foreach ($results as $row) {
        $edit_url = add_query_arg(['edit_id' => $row->id], admin_url('admin.php?page=laravel-model-management'));
        $delete_url = add_query_arg(['delete_id' => $row->id], admin_url('admin.php?page=laravel-model-management'));
        echo "<tr>
            <td>{$row->id}</td>
            <td>{$row->maker_name_id}</td>
            <td>" . esc_html($row->model_name) . "</td>
            <td>{$row->created_at}</td>
            <td>{$row->updated_at}</td>
            <td><a href='{$edit_url}' class='button'>編集</a> 
                <a href='{$delete_url}' class='button button-danger' onclick='return confirm(\"本当に削除しますか？\")'>削除</a></td>
        </tr>";
    }
    echo '</tbody></table>';

    // ページネーション
    $base_url = add_query_arg(array_filter([
        'page' => 'laravel-model-management',
        'maker_name_id' => $selected_maker,
        'model_id' => $selected_model
    ]), admin_url('admin.php'));

    echo '<div style="margin-top:20px;">';
    if ($current_page > 1) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '">&laquo; 前へ</a> ';
    }
    if ($current_page < $total_pages) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '">次へ &raquo;</a>';
    }
    echo "<p>ページ {$current_page} / {$total_pages}</p>";
    echo '</div>';

    // フォーム
    $editing_id = $editing ? intval($editing->id) : '';
    $maker_val = $editing ? intval($editing->maker_name_id) : '';
    $model_val = $editing ? esc_attr($editing->model_name) : '';
    $form_title = $editing ? '編集' : '新規追加';

    echo <<<HTML
<div id="fixed-form-toggle">✕ フォームを閉じる</div>
<div id="fixed-form-container">
    <h2 style="margin-top:0;">{$form_title}</h2>
    <form method="POST">
        <input type="hidden" name="editing_id" value="{$editing_id}">
        <p><label>メーカーID:<br><input type="number" name="maker_name_id" required value="{$maker_val}" style="width:100%;"></label></p>
        <p><label>モデル名:<br><input type="text" name="model_name" required value="{$model_val}" style="width:100%;"></label></p>
        <p><button type="submit" name="save_model" class="button button-primary" style="width:100%;">保存</button></p>
    </form>
</div>
HTML;

    echo '</div>'; // .wrap
}
