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
        $filteredMarketPricesGrade = MarketPriceMaster::with('grade')
            ->where('grade_name_id', $id)
            ->orderBy('year', 'desc')
            ->get();
    
        // min_price が 0 の場合に max_price の 65% に修正
        foreach ($filteredMarketPricesGrade as $price) {
            if ($price->min_price == 0 && $price->max_price > 0) {
                $price->min_price = $price->max_price * 0.65;
            }
        }
    
        // データを加工して必要な情報を追加
        $filteredMarketPricesGrade = $filteredMarketPricesGrade->map(function ($item) {
            return (object) [
                'grade_name_id' => $item->grade_name_id,
                'year' => $item->year,
                'mileage' => $item->mileage,
                'model_number' => optional($item->grade)->model_number, // ScGooGrade から取得
                'engine_model' => optional($item->grade)->engine_model, // ScGooGrade から取得
                'min_price' => $item->min_price, 
                'max_price' => $item->max_price,
                'sc_url' => $item->sc_url
            ];
        });
    
        return view('main.marketprice', compact('grade', 'filteredMarketPricesGrade'));
    }
    
    
}
