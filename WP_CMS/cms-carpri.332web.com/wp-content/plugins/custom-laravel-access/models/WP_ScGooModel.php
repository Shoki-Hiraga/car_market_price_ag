<?php

class WP_ScGooModel
{
    public static function getByMakerId($db, $maker_id)
    {
        return $db->get_results(
            $db->prepare("SELECT * FROM sc_goo_model WHERE maker_name_id = %d ORDER BY model_name ASC", $maker_id)
        );
    }
}
