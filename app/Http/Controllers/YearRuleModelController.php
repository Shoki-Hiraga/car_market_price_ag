<?php

namespace App\Http\Controllers;

use App\Models\ScGooGrade;
use Illuminate\Support\Facades\DB;

class YearRuleModelController extends Controller
{
    public function index($maker_name_id)
    {
        $currentYear = date('Y');
        $targetYears = [$currentYear - 25, $currentYear - 24, $currentYear - 23];

        $models = ScGooGrade::select('model_name_id')
            ->where('maker_name_id', $maker_name_id)
            ->whereIn('year', $targetYears)
            ->groupBy('model_name_id')
            ->pluck('model_name_id')
            ->toArray();

        $modelData = DB::table('sc_goo_model')
            ->whereIn('id', $models)
            ->orderBy('model_name')
            ->get();

        return view('main.year_rule_model', compact('modelData', 'maker_name_id'));
    }
}
