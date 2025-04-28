<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScGooGrade;

class YearRuleController extends Controller
{
    public function index()
    {
        $currentYear = date('Y');
        $targetYears = [$currentYear - 25, $currentYear - 24, $currentYear - 23];

        // ページネーション (1ページ50件)
        $grades = ScGooGrade::with(['maker', 'model'])
            ->whereIn('year', $targetYears)
            ->orderBy('year', 'asc')
            ->paginate(50);  // ★ここを変更！

        return view('main.year_rule', compact('grades', 'targetYears'));
    }
}
