<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooMaker;

class ScGooMakerController extends Controller
{
    public function index()
    {
        // MarketPriceMaster に存在するメーカーを取得（重複を排除）
        $sc_goo_maker = MarketPriceMaster::with('maker')
            ->get()
            ->unique('maker_name_id') // maker_name_id ごとに一意にする
            ->pluck('maker'); // maker情報のみを取得

        // MarketPriceMaster に存在するデータ数を表示
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('maker.index');

        return view('main.index', compact('sc_goo_maker', 'marketPriceCount', 'canonicalUrl'));
    }
}
