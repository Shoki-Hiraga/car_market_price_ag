<?php
if (!class_exists('wpdb')) {
    require_once(ABSPATH . 'wp-includes/wp-db.php');
}

global $laravel_db;
$laravel_db = new wpdb(
    'chasercb750_mark',
    '78195090Cb',
    'chasercb750_marketprice',
    'mysql8004.xserver.jp'
);
