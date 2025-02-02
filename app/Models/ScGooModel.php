<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScGooMaker;

class ScGooModel extends Model
{
    use HasFactory;

    protected $table = 'sc_goo_model';
    protected $fillable = ['model_name', 'maker_id'];

    // Maker モデルとのリレーションを定義
    public function maker()
    {
        return $this->belongsTo(ScGooMaker::class, 'maker_name_id', 'id');
    }

}
