<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearGrade extends Model
{
    use HasFactory;

    protected $table = 'year_grade'; // テーブル名を明示する

    protected $fillable = [
        'maker_name_id',
        'model_name_id',
        'grade_name',
        'model_number',
        'engine_model',
        'year',
        'month',
        'sc_url',
        'created_at',
        'updated_at',
    ];

    // IDはオートインクリメントのまま使う（特別な設定不要）
}
