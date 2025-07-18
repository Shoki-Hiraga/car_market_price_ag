<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketPriceMaster;
use App\Models\ScGooMaker;

class ScGooMakerController extends Controller
{
    public function index()
    {
        // MarketPriceMaster に存在するメーカーを取得（重複排除、必要項目のみ）
        $sc_goo_makers = MarketPriceMaster::with('maker')
            ->get()
            ->unique('maker_name_id')
            ->map(function ($item) {
                return (object)[
                    'maker_name_id' => optional($item->maker)->maker_name_id,
                    'mpm_maker_name' => optional($item->maker)->mpm_maker_name,
                ];
            });

        $marketPriceCount = MarketPriceMaster::count();
        $canonicalUrl = route('maker.index');

        return view('main.index', compact('sc_goo_makers', 'marketPriceCount', 'canonicalUrl'));
    }

}
