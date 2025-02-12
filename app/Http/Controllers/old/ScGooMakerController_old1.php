<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooMaker;

class ScGooMakerController extends Controller
{
    public function index()
    {
        $sc_goo_maker = ScGooMaker::select('maker_name')->get();
        return view('main.index', compact('sc_goo_maker'));
    }
}
