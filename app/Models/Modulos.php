<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulos extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_MODULOS';
    protected $fillable = [
        'ID_MODULO',
        'NOMBRE_MODULO',
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_MODULO';
}
