<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooMaker;
use App\Models\MpmMakerModel;

class MpmMakerModelController extends Controller
{
    public function index()
    {
        // MarketPriceMaster に存在するメーカー名を取得（重複を排除）
        $sc_goo_maker = MpmMakerModel::orderBy('maker_name_id')->pluck('mpm_maker_name')->unique();

        // MarketPriceMaster に存在するデータ数を表示
        $marketPriceCount = MarketPriceMaster::count();

        // 正規URLを生成
        $canonicalUrl = route('maker.index');

        return view('main.index', compact('sc_goo_maker', 'marketPriceCount', 'canonicalUrl'));
    }
}
