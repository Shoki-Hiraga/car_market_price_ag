<?php

namespace App\Http\Controllers;

use App\Models\YearGrade;
use Illuminate\Support\Facades\DB;

class YearRuleModelController extends Controller
{
    public function index($maker_name_id)
    {
        $currentYear = date('Y');
        $targetYears = [$currentYear - 26, $currentYear - 25, $currentYear - 24, $currentYear - 23];
    
        $models = YearGrade::select('model_name_id')
            ->where('maker_name_id', $maker_name_id)
            ->whereIn('year', $targetYears)
            ->groupBy('model_name_id')
            ->pluck('model_name_id')
            ->toArray();
    
        $modelData = DB::table('sc_goo_model')
            ->whereIn('id', $models)
            ->orderBy('model_name')
            ->get();
    
        // maker_name を取得
        $maker = DB::table('sc_goo_maker')
            ->where('id', $maker_name_id)
            ->first(); // or ->value('maker_name') if you want only the name
    
        return view('main.year_rule_model', [
            'modelData' => $modelData,
            'maker_name_id' => $maker_name_id,
            'maker' => $maker, // ←ここ
        ]);
    }
    
}
