<?php

namespace App\Http\Controllers;

use App\Models\ScGooGrade;

class YearRuleGradeController extends Controller
{
    public function index($maker_name_id, $model_name_id)
    {
        $currentYear = date('Y');
        $targetYears = [$currentYear - 26, $currentYear - 25, $currentYear - 24, $currentYear - 23];
    
        $grades = ScGooGrade::with(['maker', 'model'])
            ->where('model_name_id', $model_name_id)
            ->whereIn('year', $targetYears)
            ->orderBy('year', 'asc')
            ->paginate(50);
    
        return view('main.year_rule_grade', compact('grades', 'targetYears'));
    }    
}
