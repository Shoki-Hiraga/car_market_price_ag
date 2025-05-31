<?php

require_once plugin_dir_path(__DIR__) . 'models/WP_ScGooMaker.php';
require_once plugin_dir_path(__DIR__) . 'models/WP_ScGooModel.php';

global $laravel_db;

// メーカーとモデルのデータ取得
$makers = WP_ScGooMaker::all($laravel_db);
$selected_maker = isset($_GET['maker_name_id']) ? intval($_GET['maker_name_id']) : 0;
$selected_model = isset($_GET['model_name_id']) ? intval($_GET['model_name_id']) : 0;
$models = $selected_maker ? WP_ScGooModel::getByMakerId($laravel_db, $selected_maker) : [];

echo '<div class="wrap">';
echo '<h2>検索フィルター</h2>';
echo '<form method="GET">';
echo '<input type="hidden" name="page" value="laravel-model-contents">';

echo '<p><label>メーカー名: ';
echo '<select name="maker_name_id" id="maker_name_id">';
echo '<option value="">選択してください</option>';
foreach ($makers as $maker) {
    $selected = $maker->id == $selected_maker ? 'selected' : '';
    echo "<option value=\"{$maker->id}\" {$selected}>{$maker->maker_name}</option>";
}
echo '</select></label></p>';

echo '<p><label>モデル名: ';
echo '<select name="model_name_id" id="model_name_id">';
echo '<option value="">選択してください</option>';
foreach ($models as $model) {
    $selected = $model->id == $selected_model ? 'selected' : '';
    echo "<option value=\"{$model->id}\" {$selected}>{$model->model_name}</option>";
}
echo '</select></label></p>';

echo '<p><button type="submit" class="button">検索</button></p>';
echo '</form>';
echo '</div>';
