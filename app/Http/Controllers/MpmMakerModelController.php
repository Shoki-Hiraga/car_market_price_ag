<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\MpmMakerModel;

class MpmMakerModelController extends Controller
{
    public function index()
    {
        // MarketPriceMaster に存在するメーカーのIDと名前を取得（重複を排除）
        $sc_goo_makers = MpmMakerModel::select(['maker_name_id', 'mpm_maker_name'])
            ->orderBy('maker_name_id')
            ->distinct()
            ->get();

        // MarketPriceMaster に存在するデータ数を取得
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('maker.index');

        return view('main.index', compact('sc_goo_makers', 'marketPriceCount', 'canonicalUrl'));
    }
}
