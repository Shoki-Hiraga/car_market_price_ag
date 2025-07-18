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
        $makerName = null;
        $modelName = null;

        // カスタム表示名
        $customNames = [
            'maker' => 'メーカー / 車種一覧',
            'year-rule' => '25年ルール対象 メーカー一覧',
            'year-rule-all' => '25年ルール対象全一覧',
        ];

        foreach ($segments as $index => $segment) {
            $url .= '/' . $segment;

            // スキップ対象
            if (in_array(strtolower($segment), ['model', 'grade', 'y-maker', 'y-model'])) {
                continue;
            }

            $name = null;

            // 1. /maker/{maker_id}
            if ($segments[0] === 'maker' && $index === 1 && is_numeric($segment)) {
                $maker = ScGooMaker::find($segment);
                if ($maker) {
                    $makerName = $maker->maker_name;
                    // 1-1: 「メーカー / 車種一覧」を追加（手動）
                    $breadcrumb[] = [
                        'name' => $customNames['maker'],
                        'url'  => url('/maker'),
                    ];
                    $name = "{$makerName} の車種一覧";
                } else {
                    $name = "不明なメーカー";
                }
            }

            // 2. /model/{model_id}
            elseif ($segments[0] === 'model' && $index === 1 && is_numeric($segment)) {
                $model = ScGooModel::with('maker')->find($segment);
                if ($model) {
                    $maker = $model->maker;
                    $makerName = $maker->maker_name;
                    $modelName = $model->model_name;

                    // 2-1: 「メーカー / 車種一覧」追加
                    $breadcrumb[] = [
                        'name' => $customNames['maker'],
                        'url'  => url('/maker'),
                    ];
                    // 2-2: メーカーの車種一覧（/maker/{maker_id}）
                    $breadcrumb[] = [
                        'name' => "{$makerName} の車種一覧",
                        'url'  => url("/maker/{$maker->id}")
                    ];
                    // 2-3: 現在のモデル名
                    $name = "{$makerName} {$modelName}";
                } else {
                    $name = "不明なモデル";
                }
            }

            // 3. /model/{model_id}/grade/{grade_id}
            elseif (($segments[2] ?? null) === 'grade' && $index === 3 && is_numeric($segment)) {
                $grade = ScGooGrade::find($segment);
                if ($grade) {
                    $name = "{$makerName} {$modelName} {$grade->grade_name}";
                } else {
                    $name = "不明なグレード";
                }
            }

            // 4. /year-rule/y-maker/{maker_id}
            elseif ($segments[0] === 'year-rule' && $segments[1] === 'y-maker' && $index === 2 && is_numeric($segment)) {
                $maker = ScGooMaker::find($segment);
                if ($maker) {
                    $makerName = $maker->maker_name;
                    $name = "{$makerName}（25年対象）";
                } else {
                    $name = "不明なメーカー";
                }
            }

            // 5. /year-rule/y-maker/{maker_id}/y-model/{model_id}
            elseif ($segments[0] === 'year-rule' && $segments[1] === 'y-maker' && $segments[3] === 'y-model' && $index === 4 && is_numeric($segment)) {
                $model = ScGooModel::with('maker')->find($segment);
                if ($model) {
                    $makerName = $model->maker->maker_name;
                    $modelName = $model->model_name;
                    $name = "{$makerName} {$modelName}（25年対象）";
                } else {
                    $name = "不明なモデル";
                }
            }

            // 6. mileage-10 / year-2020 等
            elseif (preg_match('/^mileage-(\d+)$/i', $segment, $matches)) {
                $name = "{$matches[1]}万㎞台";
            } elseif (preg_match('/^year-(\d+)$/i', $segment, $matches)) {
                $name = "{$matches[1]}年式";
            }

            // 7. カスタム名（その他） ※ 先に処理したのでここでは除外
            elseif (isset($customNames[$segment])) {
                // 無視（処理済み）
                continue;
            }

            // 8. その他
            else {
                $name = ucfirst(str_replace('-', ' ', $segment));
            }

            // パンくずに追加
            if ($name) {
                $breadcrumb[] = [
                    'name' => $name,
                    'url'  => url($url)
                ];
            }
        }

        return $breadcrumb;
    }
}
