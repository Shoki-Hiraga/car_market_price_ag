<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooModel;
use App\Models\MarketPriceMaster;

class ScGooModelController extends Controller
{
    public function index()
    {
        // リレーションしているmakerを含む全てのデータを取得
        $sc_goo_model = ScGooModel::with('maker')->get();
        return view('main.model', compact('sc_goo_model'));
    }
    public function show($id)
    {
        // 指定されたモデルを取得
        $model = ScGooModel::with('maker')->findOrFail($id);
    
        // model_name_id に関連するグレード情報を取得
        $marketPricesMaster = MarketPriceMaster::where('model_name_id', $id)
            ->with('grade')
            ->orderBy('year', 'desc') // 年式の降順
            ->get();
    
        return view('main.model_detail', compact('model', 'marketPricesMaster'));
    }
}
