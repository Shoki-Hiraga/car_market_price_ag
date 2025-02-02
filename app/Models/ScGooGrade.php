<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScGooMaker;
use App\Models\ScGooModel;

class ScGooGrade extends Model
{
    protected $table = 'sc_goo_grade';
    protected $fillable = ['grade_name', 'model_name_id', 'maker_name_id'];

    // Maker モデルとのリレーションを定義
    public function model()
    {
        return $this->belongsTo(ScGooModel::class, 'model_name_id', 'id');
    }

    public function maker()
    {
        return $this->belongsTo(ScGooMaker::class, 'maker_name_id', 'id');
    }
}
