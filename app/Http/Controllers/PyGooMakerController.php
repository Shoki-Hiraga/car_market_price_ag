<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PyGooMaker;

class PyGooMakerController extends Controller
{
    public function index()
    {
        $sc_goo_maker = PyGooMaker::select('maker_name')->get();
        return view('main.index', compact('sc_goo_maker'));
    }
}
