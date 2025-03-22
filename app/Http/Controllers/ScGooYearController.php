<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooGrade;
use App\Models\ModelContents;

class ScGooYearController extends Controller
{
    public function show($model_id, $grade_id, $year)
    {
        // 年式フィルター付きの買取データ取得（ページネーション対応）
        $paginator = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            ->where('year', $year)
            ->with(['grade', 'maker', 'model'])
            ->orderBy('mileage', 'asc')
            ->paginate(50);

        if ($paginator->isEmpty()) {
            abort(404);
        }

        // グレード情報を取得
        $grade = ScGooGrade::where('id', $grade_id)
            ->where('model_name_id', $model_id)
            ->with('model.maker')
            ->firstOrFail();

        // 元のコレクションに対してマッピング
        $transformed = $paginator->getCollection()->map(function ($item) {
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
                'model_number' => $GradeModnumEngmod->model_number ?? '確認中',
                'engine_model' => $GradeModnumEngmod->engine_model ?? '確認中',
            ];
        });

        // 変換後のコレクションを paginator に戻す
        $paginator->setCollection($transformed);

        // 統計情報の計算
        $allMinPrices = $transformed->pluck('min_price')->filter();
        $allMaxPrices = $transformed->pluck('max_price')->filter();

        $overallMinPrice = $allMinPrices->min();
        $overallMaxPrice = $allMaxPrices->max();
        $overallAvgPrice = ($allMinPrices->avg() + $allMaxPrices->avg()) / 2;

        $minPriceMileage = $transformed->where('min_price', $overallMinPrice)->pluck('mileage')->first();
        $maxPriceMileage = $transformed->where('max_price', $overallMaxPrice)->pluck('mileage')->first();
        $avgPriceMileage = $transformed->whereBetween('min_price', [$overallMinPrice, $overallMaxPrice])
            ->pluck('mileage')
            ->avg();

        // その他情報
        $marketPriceCount = MarketPriceMaster::count();
        $canonicalUrl = route('year.detail', ['model_id' => $model_id, 'grade_id' => $grade_id, 'year' => $year]);
        $modelContent = ModelContents::where('model_name_id', $model_id)->first();

        // ビューへ渡す
        return view('main.year_detail', [
            'grade' => $grade,
            'filteredMarketPrices' => $paginator,
            'marketPriceCount' => $marketPriceCount,
            'canonicalUrl' => $canonicalUrl,
            'modelContent' => $modelContent,
            'overallMinPrice' => $overallMinPrice,
            'overallMaxPrice' => $overallMaxPrice,
            'overallAvgPrice' => $overallAvgPrice,
            'minPriceMileage' => $minPriceMileage,
            'maxPriceMileage' => $maxPriceMileage,
            'avgPriceMileage' => $avgPriceMileage,
            'year' => $year,
        ]);
    }
}
