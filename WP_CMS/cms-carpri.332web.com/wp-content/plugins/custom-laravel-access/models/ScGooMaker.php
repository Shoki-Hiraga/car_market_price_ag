<?php

class ScGooMaker
{
    public static function all($db)
    {
        return $db->get_results("SELECT * FROM sc_goo_maker ORDER BY maker_name ASC");
    }
}
