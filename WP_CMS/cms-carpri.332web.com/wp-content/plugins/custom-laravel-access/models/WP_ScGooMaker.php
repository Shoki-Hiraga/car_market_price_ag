<?php

class WP_ScGooMaker
{
    // 一覧取得
    public static function all($db)
    {
        return $db->get_results("SELECT * FROM sc_goo_maker ORDER BY maker_name ASC");
    }

    // IDから1件取得（編集時などに使用）
    public static function find($db, $id)
    {
        return $db->get_row($db->prepare("SELECT * FROM sc_goo_maker WHERE id = %d", $id));
    }

    // 新規作成
    public static function create($db, $maker_name)
    {
        $now = current_time('mysql');
        return $db->insert('sc_goo_maker', [
            'maker_name' => $maker_name,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    // 更新
    public static function update($db, $id, $maker_name)
    {
        $now = current_time('mysql');
        return $db->update('sc_goo_maker', [
            'maker_name' => $maker_name,
            'updated_at' => $now
        ], ['id' => $id]);
    }

    // 削除
    public static function delete($db, $id)
    {
        return $db->delete('sc_goo_maker', ['id' => $id]);
    }
}
