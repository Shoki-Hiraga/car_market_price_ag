<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooModel;

class ScGooModelController extends Controller
{
    public function index()
    {
        $sc_goo_model = ScGooModel::select('model_name')->get();
        return view('main.model', compact('sc_goo_model'));
    }
}
