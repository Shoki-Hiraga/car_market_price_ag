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
        // MarketPriceMaster から該当の grade_name_id を取得
        $marketPrice = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            ->first();
        
        // 存在しない場合は 404 を返す
        if (!$marketPrice) {
            abort(404);
        }
    
        // MarketPriceMaster の grade_name_id に一致する ScGooGrade の grade_name を取得
        $grade = ScGooGrade::where('id', function ($query) use ($grade_id) {
                $query->select('id')
                    ->from('sc_goo_grade')
                    ->where('id', $grade_id);
            })
            ->with('model.maker')
            ->first();
        
        // 存在しない場合は 404 を返す
        if (!$grade) {
            abort(404);
        }
        
        // そのグレードの買取価格データを取得
        $filteredMarketPricesGrade = MarketPriceMaster::where('model_name_id', $model_id)
            ->where('grade_name_id', $grade_id)
            ->where('maker_name_id', $grade->model->maker->id)
            ->orderBy('year', 'desc')
            ->get();
    
        // min_price が 0 の場合に max_price の 65% に修正
        foreach ($filteredMarketPricesGrade as $price) {
            if ($price->min_price == 0 && $price->max_price > 0) {
                $price->min_price = $price->max_price * 0.65;
            }
        }
    
        // MarketPriceMaster のデータを基に ScGooGrade の model_number と engine_model を取得
        $filteredMarketPricesGrade = $filteredMarketPricesGrade->map(function ($item) {
            $GradeModnumEngmod = ScGooGrade::where('model_name_id', $item->model_name_id)
                ->where('maker_name_id', $item->maker_name_id)
                ->where('grade_name', function ($query) use ($item) {
                    $query->select('grade_name')
                          ->from('sc_goo_grade')
                          ->where('id', $item->grade_name_id);
                })
                ->where('year', '<=', $item->year)
                ->orderBy('year', 'desc')
                ->first();
    
            return (object) [
                'grade_name_id' => $item->grade_name_id,
                'year' => $item->year,
                'mileage' => $item->mileage,
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'sc_url' => $item->sc_url,
                'model_number' => $GradeModnumEngmod ? $GradeModnumEngmod->model_number : '確認中',
                'engine_model' => $GradeModnumEngmod ? $GradeModnumEngmod->engine_model : '確認中',
            ];
        });
    
        return view('main.grade_detail', compact('grade', 'filteredMarketPricesGrade'));
    }
    
    
}
