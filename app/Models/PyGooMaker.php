<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PyGooMaker extends Model
{
    protected $table = 'sc_goo_maker';
    protected $fillable = ['maker_name'];
}
