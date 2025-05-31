<?php

require_once plugin_dir_path(__FILE__) . 'laravel-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'models/WP_ScGooMaker.php';

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_laravel-maker-management') {
        return;
    }
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
        'sc_goo_maker管理',
        'sc_goo_maker管理',
        'manage_options',
        'laravel-maker-management',
        'laravel_maker_management_page',
        'dashicons-admin-tools',
        26
    );
});

function laravel_maker_management_page() {
    global $laravel_db;

    // 削除処理
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $laravel_db->delete('sc_goo_maker', ['id' => $delete_id]);
        echo '<div class="notice notice-warning"><p>ID ' . $delete_id . ' を削除しました。</p></div>';
    }

    // 編集データ取得
    $editing = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $editing = $laravel_db->get_row($laravel_db->prepare("SELECT * FROM sc_goo_maker WHERE id = %d", $edit_id));
    }

    // 保存処理
    if (isset($_POST['save_maker'])) {
        $maker_name = sanitize_text_field($_POST['maker_name']);
        $now = current_time('mysql');

        if (!empty($_POST['editing_id'])) {
            $laravel_db->update('sc_goo_maker', [
                'maker_name' => $maker_name,
                'updated_at' => $now
            ], ['id' => intval($_POST['editing_id'])]);
            echo '<div class="notice notice-success"><p>更新しました！</p></div>';
        } else {
            $laravel_db->insert('sc_goo_maker', [
                'maker_name' => $maker_name,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            echo '<div class="notice notice-success"><p>新規追加しました！</p></div>';
        }
    }

    // 検索条件
    $search_term = isset($_GET['search_maker']) ? sanitize_text_field($_GET['search_maker']) : '';
    $where_sql = '';
    $params = [];
    if ($search_term !== '') {
        $where_sql = 'WHERE maker_name LIKE %s';
        $params[] = '%' . $laravel_db->esc_like($search_term) . '%';
    }

    // ページネーション
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 50;
    $offset = ($current_page - 1) * $per_page;

    $total_items = $laravel_db->get_var($laravel_db->prepare(
        "SELECT COUNT(*) FROM sc_goo_maker $where_sql",
        ...$params
    ));
    $total_pages = ceil($total_items / $per_page);

    $query_params = array_merge($params, [$per_page, $offset]);
    $results = $laravel_db->get_results($laravel_db->prepare(
        "SELECT * FROM sc_goo_maker $where_sql ORDER BY id DESC LIMIT %d OFFSET %d",
        ...$query_params
    ));

    echo '<div class="wrap">';
    echo '<h1>メーカー一覧</h1>';
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="laravel-maker-management">';
    echo '<p><label>メーカー名: <input type="text" name="search_maker" value="' . esc_attr($search_term) . '" /> <button type="submit" class="button">検索</button></label></p>';
    echo '</form>';

    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>ID</th><th>メーカー名</th><th>作成日</th><th>更新日</th><th>操作</th></tr></thead><tbody>';
    foreach ($results as $row) {
        $edit_link = add_query_arg(['edit_id' => $row->id], admin_url('admin.php?page=laravel-maker-management'));
        $delete_link = add_query_arg(['delete_id' => $row->id], admin_url('admin.php?page=laravel-maker-management'));
        echo "<tr>
            <td>{$row->id}</td>
            <td>" . esc_html($row->maker_name) . "</td>
            <td>{$row->created_at}</td>
            <td>{$row->updated_at}</td>
            <td><a href='{$edit_link}' class='button'>編集</a> <a href='{$delete_link}' class='button button-danger' onclick='return confirm(\"本当に削除しますか？\")'>削除</a></td>
        </tr>";
    }
    echo '</tbody></table>';

    $base_url = add_query_arg(array_filter([
        'page' => 'laravel-maker-management',
        'search_maker' => $search_term
    ]), admin_url('admin.php'));

    echo '<div style="margin-top: 20px;">';
    if ($current_page > 1) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page - 1, $base_url)) . '">&laquo; 前へ</a> ';
    }
    if ($current_page < $total_pages) {
        echo '<a class="button" href="' . esc_url(add_query_arg('paged', $current_page + 1, $base_url)) . '">次へ &raquo;</a>';
    }
    echo "<p>ページ {$current_page} / {$total_pages}</p></div>";

    // 編集フォーム
    $editing_id = $editing ? intval($editing->id) : '';
    $maker_val = $editing ? esc_attr($editing->maker_name) : '';
    $form_title = $editing ? '編集' : '新規追加';

    echo <<<HTML
<div id="fixed-form-toggle">✕ フォームを閉じる</div>
<div id="fixed-form-container">
    <h2 style="margin-top: 0;">{$form_title}</h2>
    <form method="POST">
        <input type="hidden" name="editing_id" value="{$editing_id}">
        <p><label>メーカー名:<br><input type="text" name="maker_name" required value="{$maker_val}" style="width:100%;"></label></p>
        <p><button type="submit" name="save_maker" class="button button-primary" style="width:100%;">保存</button></p>
    </form>
</div>
HTML;
}
