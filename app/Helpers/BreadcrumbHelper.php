<?php

namespace App\Helpers;

use App\Models\ScGooMaker;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;

class BreadcrumbHelper
{
    public static function generate()
    {
        $segments = request()->segments();
        $breadcrumb = [];
        $url = '';

        // スラグ名と表示名のマッピング
        $customNames = [
            'model' => 'メーカー / 車種一覧', // "model" を "車種一覧" に変更
            'year-rule' => '25年ルール対象 メーカー一覧',
            'year-rule-all' => '25年ルール対象全一覧'
        ];

        $modelName = null;
        $makerName = null;

        foreach ($segments as $index => $segment) {
            // 先にURLを積み上げる（全セグメントを含む）
            $url .= '/' . $segment;
        
            // 表示をスキップするセグメント（URL構築には含める）
            if (strtolower($segment) === 'grade') {
                continue;
            }
            if (strtolower($segment) === 'y-maker') {
                continue;
            }
            if (strtolower($segment) === 'y-model') {
                continue;
            }
        
            // mileage-10 のような形式にマッチする場合
            if (preg_match('/^mileage-(\d+)$/i', $segment, $matches)) {
                $distance = $matches[1];
                $name = "{$distance}万㎞台";
            }
            // year-2020 のような形式にマッチする場合
            elseif (preg_match('/^year-(\d+)$/i', $segment, $matches)) {
                $distance = $matches[1];
                $name = "{$distance}年式";
            }

            // カスタム名があれば適用
            elseif (isset($customNames[$segment])) {
                $name = $customNames[$segment];
            }

            // モデルID部分
            elseif ($index === 1 && is_numeric($segment)) {
                $model = ScGooModel::with('maker')->find($segment);
                if ($model) {
                    $makerName = $model->maker->maker_name;
                    $modelName = $model->model_name;
                    $name = "{$makerName} {$modelName}";
                } else {
                    $name = "不明なモデル";
                }
            }
            // グレードID部分
            elseif ($index === 3 && is_numeric($segment)) {
                $grade = ScGooGrade::find($segment);
                if ($grade) {
                    $name = "{$makerName} {$modelName} {$grade->grade_name}";
                } else {
                    $name = "不明なグレード";
                }
            }
            // メーカーID部分 （y-maker）
            elseif ($index === 2 && is_numeric($segment)) {
                $maker = ScGooMaker::find($segment);
                if ($maker) {
                    $makerName = $maker->maker_name;
                    $name = $makerName;
                } else {
                    $name = "不明なメーカー";
                }
            }
            // モデルID部分（y-model）
            elseif ($index === 4 && is_numeric($segment)) {
                $model = ScGooModel::with('maker')->find($segment);
                if ($model) {
                    $makerName = $model->maker->maker_name;
                    $modelName = $model->model_name;
                    $name = "{$makerName} {$modelName}";
                } else {
                    $name = "不明なモデル";
                }
            }


            // それ以外はデフォルト変換
            else {
                $name = ucfirst(str_replace('-', ' ', $segment));
            }
        
            // パンくずに追加
            $breadcrumb[] = [
                'name' => $name,
                'url'  => url($url)
            ];
        }
        
        return $breadcrumb;
    }
}
