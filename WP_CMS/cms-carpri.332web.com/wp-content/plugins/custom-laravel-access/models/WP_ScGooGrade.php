<?php

class WP_ScGooGrade
{
    public static function all($db)
    {
        return $db->get_results("SELECT * FROM sc_goo_grade ORDER BY id DESC");
    }

    public static function find($db, $id)
    {
        return $db->get_row($db->prepare("SELECT * FROM sc_goo_grade WHERE id = %d", $id));
    }
}
