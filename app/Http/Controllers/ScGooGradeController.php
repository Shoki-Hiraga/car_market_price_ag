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

    public function show($model_id, $grade_id)
    {
        // `$model_id` と `$grade_id` の組み合わせが正しいかチェック
        $grade = ScGooGrade::where('id', $grade_id)
            ->where('model_name_id', $model_id) // ここで model_id もチェック
            ->with('model.maker')
            ->first();
    
        // 存在しない組み合わせなら 404 エラー
        if (!$grade) {
            abort(404);
        }
    
        // そのグレードの買取価格データを取得（model_id も条件に追加）
        $filteredMarketPricesGrade = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            ->where('maker_name_id', $grade->model->maker->id) // maker_name_id を条件に追加
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
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'sc_url' => $item->sc_url
            ];
        });
    
        return view('main.grade_detail', compact('grade', 'filteredMarketPricesGrade'));
    }
        
    
    
}
