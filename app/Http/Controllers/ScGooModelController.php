<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;

class ScGooModelController extends Controller
{
    public function index()
    {
        // リレーションしているmakerを含む全てのデータを取得
        $sc_goo_model = ScGooModel::with('maker')->get();
        return view('main.model', compact('sc_goo_model'));
    }
    public function show($id)
    {
        // 指定されたモデルを取得
        $model = ScGooModel::with('maker')->findOrFail($id);
        // ScGooGrade から model_number と engine_model を取得

        $grade = ScGooGrade::where('model_name_id', $id)->first();
        // もし grade のデータが存在すれば、それを追加
        if ($grade) {
            $model->model_number = $grade->model_number;
            $model->engine_model = $grade->engine_model;
        }
 
        // model_name_id に関連するグレード情報を取得
        $marketPricesMaster = MarketPriceMaster::where('model_name_id', $id)
            ->with('grade')
            ->orderBy('year', 'desc') // 年式の降順
            ->get();

        // グレード名と年式でグループ化し、min_priceの最小値、max_priceの最大値を取得
        $filteredMarketPrices = $marketPricesMaster
        ->groupBy(function ($item) {
            return $item->grade_name_id . '_' . $item->year; // グレードと年式でグループ化
        })
        ->map(function ($group) {
            return (object) [ // オブジェクトに変換して Blade で扱いやすくする
                'grade_name_id' => $group->first()->grade_name_id,
                'grade' => $group->first()->grade, // 最初のデータを採用
                'year' => $group->first()->year,
                'mileage' => $group->first()->mileage, // 走行距離は最初のデータを使う
                'min_price' => $group->min('min_price'), // 最小値
                'max_price' => $group->max('max_price'), // 最大値
                'sc_url' => $group->first()->sc_url // 詳細URLは最初のデータを使用
            ];
        })->values(); // 配列のキーをリセット

        return view('main.model_detail', compact('model', 'filteredMarketPrices'));
    }
}
