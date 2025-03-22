<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooGrade;
use App\Models\ModelContents;

class ScGooMileageController extends Controller
{
    public function show($model_id, $grade_id, $mileage_category)
    {
        // 走行距離が「11万km台」なら → 11.0〜11.9 までを対象とする
        $mileageMin = $mileage_category * 1.0;           // 例: 11 → 11.0
        $mileageMax = $mileageMin + 0.9;                 // → 11.9

        $marketPrices = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            ->whereBetween('mileage', [$mileageMin, $mileageMax])
            ->with(['grade', 'maker', 'model'])
            ->orderBy('year', 'desc')
            ->paginate(50);

        // dd($mileageMin, $mileageMax, $marketPrices->count());

        if ($marketPrices->isEmpty()) {
            abort(404);
        }

        // **該当するグレード情報を取得**
        $grade = ScGooGrade::where('id', $grade_id)
            ->where('model_name_id', $model_id)
            ->with('model.maker')
            ->firstOrFail();

        // **MarketPriceMaster のデータを処理**
        $filteredMarketPrices = $marketPrices->map(function ($item) {
            $GradeModnumEngmod = ScGooGrade::where('model_name_id', $item->model_name_id)
                ->where('maker_name_id', $item->maker_name_id)
                ->where('grade_name', function ($query) use ($item) {
                    $query->select('grade_name')
                          ->from('sc_goo_grade')
                          ->where('id', $item->grade_name_id);
                })
                ->where('year', '<=', $item->year)
                ->orderBy('year', 'desc')
                ->first();

            return (object) [
                'grade_name_id' => $item->grade_name_id,
                'year' => $item->year,
                'mileage' => $item->mileage,
                'min_price' => $item->min_price ?: ($item->max_price > 0 ? $item->max_price * 0.65 : 0),
                'max_price' => $item->max_price,
                'sc_url' => $item->sc_url,
                'model_number' => $GradeModnumEngmod ? $GradeModnumEngmod->model_number : '確認中',
                'engine_model' => $GradeModnumEngmod ? $GradeModnumEngmod->engine_model : '確認中',
            ];
        });

        // **価格の統計情報を計算**
        $allMinPrices = $filteredMarketPrices->pluck('min_price')->filter();
        $allMaxPrices = $filteredMarketPrices->pluck('max_price')->filter();

        $overallMinPrice = $allMinPrices->min();
        $overallMaxPrice = $allMaxPrices->max();
        $overallAvgPrice = ($allMinPrices->avg() + $allMaxPrices->avg()) / 2;

        // **走行距離の統計情報を取得**
        $minPriceMileage = $filteredMarketPrices->where('min_price', $overallMinPrice)->pluck('mileage')->first();
        $maxPriceMileage = $filteredMarketPrices->where('max_price', $overallMaxPrice)->pluck('mileage')->first();
        $avgPriceMileage = $filteredMarketPrices->whereBetween('min_price', [$overallMinPrice, $overallMaxPrice])
            ->pluck('mileage')
            ->avg();

        // MarketPriceMaster に存在するデータ数
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('mileage.detail', ['model_id' => $model_id, 'grade_id' => $grade_id, 'mileage_category' => $mileage_category]);

        // ModelContents からデータを取得
        $modelContent = ModelContents::where('model_name_id', $model_id)->first();

        return view('main.mileage_detail', compact(
            'grade', 
            'filteredMarketPrices', 
            'marketPriceCount', 
            'canonicalUrl', 
            'modelContent', 
            'overallMinPrice', 
            'overallMaxPrice', 
            'overallAvgPrice',
            'minPriceMileage',
            'maxPriceMileage',
            'avgPriceMileage',
            'mileage_category'
        ));
    }
}
