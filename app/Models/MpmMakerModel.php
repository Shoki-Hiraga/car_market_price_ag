<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpmMakerModel extends Model
{
    protected $table = 'mpm_maker_model';
    protected $fillable = ['maker_name_id', 'maker_name', 'model_name_id', 'model_name'];
}
