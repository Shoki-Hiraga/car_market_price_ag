<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScGooMaker;
use App\Models\ScGooModel;

class ModelContents extends Model
{
    protected $table = 'model_contents';
    protected $fillable = ['maker_name_id', 'model_name_id', 'model_text_content'];

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
