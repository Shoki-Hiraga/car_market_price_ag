<?php

require_once plugin_dir_path(__FILE__) . 'laravel-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'models/ScGooMaker.php';
require_once plugin_dir_path(__FILE__) . 'models/ScGooModel.php';
require_once plugin_dir_path(__FILE__) . 'models/ScGooGrade.php';

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_laravel-grade-management') return;

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
        'sc_goo_grade管理',
        'sc_goo_grade管理',
        'manage_options',
        'laravel-grade-management',
        'laravel_grade_management_page',
        'dashicons-list-view',
        28
    );
});

function laravel_grade_management_page()
{
    global $laravel_db;

    // 削除処理
    if (isset($_GET['delete_id'])) {
        $laravel_db->delete('sc_goo_grade', ['id' => intval($_GET['delete_id'])]);
        echo '<div class="notice notice-warning"><p>ID ' . intval($_GET['delete_id']) . ' を削除しました。</p></div>';
    }

    // 編集データ取得
    $editing = null;
    if (isset($_GET['edit_id'])) {
        $editing = $laravel_db->get_row($laravel_db->prepare("SELECT * FROM sc_goo_grade WHERE id = %d", intval($_GET['edit_id'])));
    }

    // 保存処理
    if (isset($_POST['save_grade'])) {
        $data = [
            'maker_name_id' => intval($_POST['maker_name_id']),
            'model_name_id' => intval($_POST['model_name_id']),
            'grade_name' => sanitize_text_field($_POST['grade_name']),
            'model_number' => sanitize_text_field($_POST['model_number']),
            'engine_model' => sanitize_text_field($_POST['engine_model']),
            'year' => intval($_POST['year']),
            'month' => intval($_POST['month']),
            'sc_url' => esc_url_raw($_POST['sc_url']),
            'updated_at' => current_time('mysql')
        ];

        if (!empty($_POST['editing_id'])) {
            $laravel_db->update('sc_goo_grade', $data, ['id' => intval($_POST['editing_id'])]);
            echo '<div class="notice notice-success"><p>更新しました！</p></div>';
        } else {
            $data['created_at'] = current_time('mysql');
            $laravel_db->insert('sc_goo_grade', $data);
            echo '<div class="notice notice-success"><p>新規追加しました！</p></div>';
        }
    }

    $selected_maker = isset($_GET['maker_name_id']) ? intval($_GET['maker_name_id']) : '';
    $selected_model = isset($_GET['model_name_id']) ? intval($_GET['model_name_id']) : '';
    $makers = ScGooMaker::all($laravel_db);
    $models = $selected_maker ? ScGooModel::getByMakerId($laravel_db, $selected_maker) : [];

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

    $results = $laravel_db->get_results(
        $laravel_db->prepare("SELECT * FROM sc_goo_grade {$where_sql} ORDER BY id DESC LIMIT 50", ...$params)
    );

    echo '<div class="wrap"><h1>グレード一覧</h1>';
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="laravel-grade-management">';
    echo '<p><label>メーカー名: <select name="maker_name_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($makers as $maker) {
        $selected = $maker->id == $selected_maker ? 'selected' : '';
        echo "<option value='{$maker->id}' {$selected}>{$maker->maker_name}</option>";
    }
    echo '</select></label></p>';

    echo '<p><label>モデル名: <select name="model_name_id" onchange="this.form.submit()">';
    echo '<option value="">選択してください</option>';
    foreach ($models as $model) {
        $selected = $model->id == $selected_model ? 'selected' : '';
        echo "<option value='{$model->id}' {$selected}>{$model->model_name}</option>";
    }
    echo '</select></label></p>';
    echo '</form>';

    echo '<table class="widefat"><thead><tr>
        <th>ID</th><th>メーカーID</th><th>モデルID</th><th>グレード名</th>
        <th>型式</th><th>エンジン型式</th><th>年</th><th>月</th><th>URL</th><th>操作</th>
    </tr></thead><tbody>';
    foreach ($results as $row) {
        $edit_url = add_query_arg(['edit_id' => $row->id], admin_url('admin.php?page=laravel-grade-management'));
        $delete_url = add_query_arg(['delete_id' => $row->id], admin_url('admin.php?page=laravel-grade-management'));
        echo "<tr>
            <td>{$row->id}</td><td>{$row->maker_name_id}</td><td>{$row->model_name_id}</td>
            <td>" . esc_html($row->grade_name) . "</td><td>" . esc_html($row->model_number) . "</td>
            <td>" . esc_html($row->engine_model) . "</td><td>{$row->year}</td><td>{$row->month}</td>
            <td><a href='" . esc_url($row->sc_url) . "' target='_blank'>リンク</a></td>
            <td><a class='button' href='{$edit_url}'>編集</a> 
                <a class='button button-danger' href='{$delete_url}' onclick='return confirm(\"本当に削除しますか？\")'>削除</a></td>
        </tr>";
    }
    echo '</tbody></table>';

    $form_vals = $editing ? (array)$editing : [
        'id' => '', 'maker_name_id' => '', 'model_name_id' => '', 'grade_name' => '',
        'model_number' => '', 'engine_model' => '', 'year' => '', 'month' => '', 'sc_url' => ''
    ];

    $form_title = $editing ? '編集' : '新規追加';

    echo '<div id="fixed-form-toggle">✕ フォームを閉じる</div>';
    echo '<div id="fixed-form-container">';
    echo '<h2 style="margin-top:0;">' . $form_title . '</h2>';
    echo '<form method="POST">';
    echo '<input type="hidden" name="editing_id" value="' . esc_attr($form_vals['id']) . '">';
    echo '<p><label>メーカーID:<br><input type="number" name="maker_name_id" required value="' . esc_attr($form_vals['maker_name_id']) . '"></label></p>';
    echo '<p><label>モデルID:<br><input type="number" name="model_name_id" required value="' . esc_attr($form_vals['model_name_id']) . '"></label></p>';
    echo '<p><label>グレード名:<br><input type="text" name="grade_name" required value="' . esc_attr($form_vals['grade_name']) . '"></label></p>';
    echo '<p><label>型式:<br><input type="text" name="model_number" value="' . esc_attr($form_vals['model_number']) . '"></label></p>';
    echo '<p><label>エンジン型式:<br><input type="text" name="engine_model" value="' . esc_attr($form_vals['engine_model']) . '"></label></p>';
    echo '<p><label>年:<br><input type="number" name="year" value="' . esc_attr($form_vals['year']) . '"></label></p>';
    echo '<p><label>月:<br><input type="number" name="month" value="' . esc_attr($form_vals['month']) . '"></label></p>';
    echo '<p><label>sc_url:<br><input type="url" name="sc_url" value="' . esc_attr($form_vals['sc_url']) . '"></label></p>';
    echo '<p><button class="button button-primary" type="submit" name="save_grade">保存</button></p>';
    echo '</form>';
    echo '</div>';

    echo '</div>';
}
