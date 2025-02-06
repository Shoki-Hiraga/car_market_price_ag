<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooModel;

class MarketPriceMasterController extends Controller
{
    public function show($id)
    {
        // 選択されたモデル情報を取得
        $model = ScGooModel::with('maker')->findOrFail($id);

        // market_price_master テーブルから該当するデータを取得
        $marketPricesMaster = MarketPriceMaster::where('model_name_id', $id)
            ->with(['maker', 'model', 'grade'])
            ->orderBy('year', 'desc')
            ->get();

        // ビューにデータを渡す
        return view('main.marketprice', compact('model', 'marketPricesMaster'));
    }
}
