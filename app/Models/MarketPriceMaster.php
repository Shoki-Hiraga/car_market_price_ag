<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPriceMaster extends Model
{
    use HasFactory;

    protected $table = 'market_price_master';

    protected $fillable = [
        'maker_name_id', 'model_name_id', 'grade_name_id',
        'year', 'mileage', 'min_price', 'max_price', 'sc_url'
    ];
    protected $casts = [
        'mileage' => 'float',
    ];
    public function maker()
    {
        return $this->belongsTo(ScGooMaker::class, 'maker_name_id');
    }

    public function model()
    {
        return $this->belongsTo(ScGooModel::class, 'model_name_id');
    }

    public function grade()
    {
        return $this->belongsTo(ScGooGrade::class, 'grade_name_id');
    }
}
