<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;

class MarketPriceMasterController extends Controller
{
    public function index()
    {
        // market_price_master のレコード数を取得
        $marketPriceCount = MarketPriceMaster::count();

        // ビューにデータを渡す
        return view('components.marketprice', compact('marketPriceCount'));
    }
}
