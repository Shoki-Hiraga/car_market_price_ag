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
        // MarketPriceMaster からデータ取得
        $marketPricesMaster = MarketPriceMaster::where('model_name_id', $id)
            ->whereHas('grade', function ($query) use ($id) {
                $query->where('model_name_id', $id);
            }) // ここで grade の model_id が一致するかチェック
            ->with(['grade', 'maker', 'model'])
            ->orderBy('grade_name_id', 'desc')
            ->orderBy('year', 'desc')
            ->get();
    
        // データがない場合は 404
        if ($marketPricesMaster->isEmpty()) {
            abort(404);
        }
    
        // 1つ目のデータからモデル情報を取得
        $model = $marketPricesMaster->first()->model;
    
        // グレード名と年式でグループ化し、最小価格と最大価格を取得
        $filteredMarketPricesModel = $marketPricesMaster
            ->groupBy(function ($item) {
                return $item->grade_name_id . '_' . $item->year;
            })
            ->map(function ($group) {
                $minPrice = $group->min('min_price');
                $maxPrice = $group->max('max_price');
    
                if ($minPrice == 0 && $maxPrice > 0) {
                    $minPrice = $maxPrice * 0.65;
                }
    
                return (object) [
                    'id' => $group->first()->id,
                    'model_name_id' => $group->first()->model_name_id,
                    'grade_name_id' => $group->first()->grade_name_id,
                    'maker' => $group->first()->maker,
                    'model' => $group->first()->model,
                    'grade' => $group->first()->grade,
                    'year' => $group->first()->year,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                ];
            })->values();
    
        return view('main.model_detail', compact('model', 'filteredMarketPricesModel'));
    }
    
    
}
