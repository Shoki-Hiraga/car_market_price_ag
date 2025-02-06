<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooGrade;
use App\Models\MarketPriceMaster;

class ScGooGradeController extends Controller
{
    public function index()
    {
        $sc_goo_grade = ScGooGrade::with('maker')->get();
        return view('main.grade', compact('sc_goo_grade'));
    }
    public function show($id)
    {
        // 指定されたグレードを取得
        $grade = ScGooGrade::findOrFail($id);

        // そのグレードの買取価格データを取得
        $marketPrices = MarketPriceMaster::where('grade_name_id', $id)
            ->orderBy('year', 'desc')
            ->get();

        return view('main.marketprice', compact('grade', 'marketPrices'));
    }
}
