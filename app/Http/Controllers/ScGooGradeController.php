<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooGrade;

class ScGooGradeController extends Controller
{
    public function index()
    {
        $sc_goo_grade = ScGooGrade::select('grade_name')->get();
        return view('main.grade', compact('sc_goo_grade'));
    }
}
