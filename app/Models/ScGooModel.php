<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScGooModel extends Model
{
    protected $table = 'sc_goo_model';
    protected $fillable = ['model_name'];
}
