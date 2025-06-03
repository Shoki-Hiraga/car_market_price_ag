<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooModel;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;
use App\Models\ModelContents;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ScGooModelController extends Controller
{
    public function index()
    {
        // MarketPriceMaster に登録されているメーカーとモデルを取得
        $existingMarketPriceModels = MarketPriceMaster::whereHas('grade', function ($query) {
                $query->whereColumn('model_name_id', 'market_price_master.model_name_id');
            })
            ->whereHas('maker', function ($query) {
                $query->whereColumn('id', 'market_price_master.maker_name_id');
            })
            ->with(['maker', 'model']) // 関連するデータを取得
            ->orderBy('id', 'asc')
            ->get()
            ->unique('model_name_id'); // model_name_id ごとに一意にする

        // メーカーごとにグループ化し、各グループ内のモデル名をソート
        $groupedMarketPriceModels = $existingMarketPriceModels
            ->groupBy(fn($item) => optional($item->maker)->maker_name)
            ->map(fn($models) => $models->sortBy(fn($item) => optional($item->model)->model_name, SORT_NATURAL));

        // MarketPriceMaster に存在するデータ数を表示
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('model.index');

        return view('main.model', compact('groupedMarketPriceModels', 'marketPriceCount', 'canonicalUrl'));
    }
    
    public function show($id)
    {
        // MarketPriceMaster からデータ取得
        $marketPricesMaster = MarketPriceMaster::where('model_name_id', $id)
            ->whereHas('grade', function ($query) use ($id) {
                $query->where('model_name_id', $id);
            }) // ここで grade の model_id が一致するかチェック
            ->whereHas('maker', function ($query) use ($id) {
                $query->whereIn('id', function ($subQuery) use ($id) {
                    // MarketPriceMaster の maker_name_id が一致するものを取得
                    $subQuery->select('maker_name_id')
                        ->from('market_price_master') // 正しいテーブル名を指定
                        ->where('model_name_id', $id);
                });
            })
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

        // **価格の統計情報を計算**
        $allMinPrices = $filteredMarketPricesModel->pluck('min_price')->filter();
        $allMaxPrices = $filteredMarketPricesModel->pluck('max_price')->filter();

        $overallMinPrice = $allMinPrices->min();
        $overallMaxPrice = $allMaxPrices->max();
        $overallAvgPrice = ($allMinPrices->avg() + $allMaxPrices->avg()) / 2;

        // MarketPriceMaster に存在するデータ数を表示
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('model.detail', ['id' => $id]);

        // **ModelContents からデータを取得**
        $modelContent = ModelContents::where('model_name_id', $id)->first();

        // 手動でページネーションを適用
        $currentPage = Paginator::resolveCurrentPage();
        $perPage = 50;
        $pagedData = $filteredMarketPricesModel->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $filteredMarketPricesModel = new LengthAwarePaginator(
            $pagedData,
            $filteredMarketPricesModel->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        // chart.js 用のデータを整形（年式ごとの min/max）
        $chartData = $marketPricesMaster
            ->groupBy('year')
            ->map(function ($group) {
                return [
                    'year' => $group->first()->year,
                    'min_price' => $group->min('min_price'),
                    'max_price' => $group->max('max_price'),
                ];
            })
            ->sortBy('year')
            ->values(); // 年式順に整列

        return view('main.model_detail', compact(
            'model', 
            'filteredMarketPricesModel', 
            'marketPriceCount', 
            'canonicalUrl', 
            'modelContent', 
            'overallMinPrice', 
            'overallMaxPrice', 
            'overallAvgPrice',
            "chartData"
        ));
    }
    
    
}
