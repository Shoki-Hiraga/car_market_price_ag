<?php

namespace App\Http\Controllers;

use App\Models\ScGooGrade;
use Illuminate\Support\Facades\DB;

class YearRuleMakerController extends Controller
{
    public function index()
    {
        $currentYear = date('Y');
        $targetYears = [$currentYear - 25, $currentYear - 24, $currentYear - 23];

        $makers = ScGooGrade::select('maker_name_id')
            ->whereIn('year', $targetYears)
            ->groupBy('maker_name_id')
            ->pluck('maker_name_id')
            ->toArray();

        $makerData = DB::table('sc_goo_maker')
            ->whereIn('id', $makers)
            ->orderBy('maker_name')
            ->get();

        return view('main.year_rule_maker', compact('makerData'));
    }
}
