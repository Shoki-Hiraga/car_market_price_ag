<?php
/*
Plugin Name: Laravel DB Access
*/

require_once plugin_dir_path(__FILE__) . 'laravel-db-connection.php';
require_once plugin_dir_path(__FILE__) . 'model_contents.php';
require_once plugin_dir_path(__FILE__) . 'model_maker.php';

add_action('wp_ajax_get_models_by_maker', 'get_models_by_maker');
function get_models_by_maker() {
    require_once plugin_dir_path(__FILE__) . 'models/ScGooModel.php';

    $maker_id = intval($_POST['maker_id']);
    $models = ScGooModel::findByMaker($maker_id);

    wp_send_json($models);

}

