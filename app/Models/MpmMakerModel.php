<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpmMakerModel extends Model
{
    protected $table = 'mpm_maker_model';
    protected $fillable = ['mpm_maker_name', 'maker_name_id', 'mpm_model_name', 'model_name_id'];
}
