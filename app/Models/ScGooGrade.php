<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScGooGrade extends Model
{
    protected $table = 'sc_goo_grade';
    protected $fillable = ['grade_name'];
}
