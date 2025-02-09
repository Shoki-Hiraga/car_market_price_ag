<?php

namespace App\Helpers;

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
            'model' => '車種一覧', // "model" を "車種一覧" に変更
        ];

        $modelName = null;
        $makerName = null;

        foreach ($segments as $index => $segment) {
            // `grade` というセグメントはスキップ（非表示）
            if (strtolower($segment) === 'grade') {
                continue;
            }

            $url .= '/' . $segment;

            // カスタム名があれば適用
            if (isset($customNames[$segment])) {
                $name = $customNames[$segment];
            } elseif ($index === 1 && is_numeric($segment)) {
                // モデル詳細ページ（/model/{id} の {id} 部分）
                $model = ScGooModel::with('maker')->find($segment);
                if ($model) {
                    $makerName = $model->maker->maker_name;
                    $modelName = $model->model_name;
                    $name = "{$makerName} {$modelName}";
                } else {
                    $name = "不明なモデル";
                }
            } elseif ($index === 3 && is_numeric($segment)) {
                // グレード詳細ページ（/model/{model_id}/grade/{grade_id} の {grade_id} 部分）
                $grade = ScGooGrade::find($segment);
                if ($grade) {
                    $name = "{$makerName} {$modelName} {$grade->grade_name}";
                } else {
                    $name = "不明なグレード";
                }
            } else {
                $name = ucfirst(str_replace('-', ' ', $segment));
            }

            $breadcrumb[] = [
                'name' => $name,
                'url'  => url($url)
            ];
        }

        return $breadcrumb;
    }
}
