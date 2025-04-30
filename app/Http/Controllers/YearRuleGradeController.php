<?php

namespace App\Http\Controllers;

use App\Models\ScGooGrade;

namespace App\Http\Controllers;
use App\Models\ScGooGrade;

class YearRuleGradeController extends Controller
{
    public function index($maker_name_id, $model_name_id)
    {
        $targetYears = range(date('Y') - 26, date('Y') - 23);

        $grades = ScGooGrade::with(['maker', 'model'])
            ->where('model_name_id', $model_name_id)
            ->whereIn('year', $targetYears)
            ->orderBy('year', 'asc')
            ->paginate(50);

        $firstGrade = $grades->first();
        $maker = $firstGrade?->maker;
        $model = $firstGrade?->model;

        return view('main.year_rule_grade', compact('grades', 'targetYears', 'maker', 'model'));
    }
}

