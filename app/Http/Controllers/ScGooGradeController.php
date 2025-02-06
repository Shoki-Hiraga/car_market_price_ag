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
        $marketPricesMasterGrade = MarketPriceMaster::where('grade_name_id', $id)
            ->orderBy('year', 'desc')
            ->get();
    
        // グレードごとに min_price の最小値、max_price の最大値を取得し、min_price が 0 の場合に修正
        $filteredMarketPrices = $marketPricesMasterGrade
            ->groupBy(function ($item) {
                return $item->grade_name_id . '_' . $item->year; // グレードと年式でグループ化
            })
            ->map(function ($group) {
                $minPrice = $group->min('min_price');
                $maxPrice = $group->max('max_price');
    
                // min_price が 0 の場合は max_price の 50% を設定
                if ($minPrice == 0 && $maxPrice > 0) {
                    $minPrice = $maxPrice * 0.65;
                }
    
                return (object) [
                    'grade_name_id' => $group->first()->grade_name_id,
                    'year' => $group->first()->year,
                    'mileage' => $group->first()->mileage, // 走行距離は最初のデータを使う
                    'min_price' => $minPrice, // 0 の場合は max_price の 50% に変更
                    'max_price' => $maxPrice, // 最大値
                    'sc_url' => $group->first()->sc_url // 詳細URLは最初のデータを使用
                ];
            })->values(); // 配列のキーをリセット
    
        return view('main.marketprice', compact('grade', 'filteredMarketPrices'));
    }
    
}
