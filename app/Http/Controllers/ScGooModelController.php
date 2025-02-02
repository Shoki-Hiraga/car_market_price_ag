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
    public function show($id)
    {
        // 指定されたIDのモデルを取得（見つからない場合は404）
        $model = ScGooModel::findOrFail($id);
        $model = ScGooModel::with('maker')->findOrFail($id);
        // 新たに作成するビュー 'model_detail' に渡す
        return view('main.model_detail', compact('model'));
    }
}
