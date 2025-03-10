<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;
use App\Models\ModelContents;

class ScGooGradeController extends Controller
{
    public function index()
    {
        $sc_goo_grade = ScGooGrade::with('maker')->get();
        return view('main.grade', compact('sc_goo_grade'));
    }

    public function show($model_id, $grade_id)
    {
        // MarketPriceMaster テーブルから、指定されたモデル ID とグレード ID に対応するデータを取得
        $marketPrices = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            
            // 関連する grade の model_id が指定された model_id と一致し、
            // さらに指定された grade_id に該当するもののみ取得
            ->whereHas('grade', function ($query) use ($model_id, $grade_id) {
                $query->where('model_name_id', $model_id)
                      ->where('id', $grade_id);
            })
            
            // MarketPriceMaster の maker_name_id が model_id に対応する maker_id と一致するもののみ取得
            ->whereHas('maker', function ($query) use ($model_id) {
                $query->whereIn('id', function ($subQuery) use ($model_id) {
                    $subQuery->select('maker_name_id')
                             ->from('market_price_master')
                             ->where('model_name_id', $model_id);
                });
            })
            
            // 関連する grade, maker, model 情報を事前にロード
            ->with(['grade', 'maker', 'model'])
                    ->orderBy('year', 'desc')  // 年の降順で並べ替え
            ->get();
    
        // 該当するデータがない場合は 404 エラーページを表示
        if ($marketPrices->isEmpty()) {
            abort(404);
        }
    
        // 取得したグレード情報を `ScGooGrade` から取得
        $grade = ScGooGrade::where('id', $grade_id)
            ->where('model_name_id', $model_id)
            ->with('model.maker')
            ->firstOrFail(); // `firstOrFail()` で存在しない場合は 404 を自動返却
    
        // MarketPriceMaster のデータを元に詳細情報を取得
        $filteredMarketPricesGrade = $marketPrices->map(function ($item) {
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
                'min_price' => $item->min_price ?: ($item->max_price > 0 ? $item->max_price * 0.65 : 0), // min_price の補正
                'max_price' => $item->max_price,
                'sc_url' => $item->sc_url,
                'model_number' => $GradeModnumEngmod ? $GradeModnumEngmod->model_number : '確認中',
                'engine_model' => $GradeModnumEngmod ? $GradeModnumEngmod->engine_model : '確認中',
            ];
        });

    // **価格の統計情報を計算**
    $allMinPrices = $filteredMarketPricesGrade->pluck('min_price')->filter();
    $allMaxPrices = $filteredMarketPricesGrade->pluck('max_price')->filter();

    $overallMinPrice = $allMinPrices->min();
    $overallMaxPrice = $allMaxPrices->max();
    $overallAvgPrice = ($allMinPrices->avg() + $allMaxPrices->avg()) / 2;

    // **走行距離の統計情報を取得**
    $minPriceMileage = $filteredMarketPricesGrade->where('min_price', $overallMinPrice)->pluck('mileage')->first();
    $maxPriceMileage = $filteredMarketPricesGrade->where('max_price', $overallMaxPrice)->pluck('mileage')->first();
    $avgPriceMileage = $filteredMarketPricesGrade->whereBetween('min_price', [$overallMinPrice, $overallMaxPrice])
    ->pluck('mileage')
    ->avg();


        // MarketPriceMaster に存在するデータ数を表示
        $marketPriceCount = MarketPriceMaster::count();
 
        // 正規URLを生成
        $canonicalUrl = route('grade.detail', ['model_id' => $model_id, 'grade_id' => $grade_id]);
 
        // **ModelContents からデータを取得**
        $modelContent = ModelContents::where('model_name_id', $model_id)->first();

        return view('main.grade_detail', compact(
            'grade', 
            'filteredMarketPricesGrade', 
            'marketPriceCount', 
            'canonicalUrl', 
            'modelContent', 
            'overallMinPrice', 
            'overallMaxPrice', 
            'overallAvgPrice',
            'minPriceMileage',
            'maxPriceMileage',
            'avgPriceMileage'
        ));
    }
        
    
}
