<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ScGooModel;

class ScGooModelController extends Controller
{
    public function index()
    {
        // リレーションしているmakerを含む全てのデータを取得
        $sc_goo_model = ScGooModel::with('maker')->get();
        return view('main.model', compact('sc_goo_model'));
    }
}
