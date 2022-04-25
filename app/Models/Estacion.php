<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacion extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ESTACION';
    protected $fillable = [
        'ID_ESTACION',
        'INVIERNO',
        'VERANO'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = '(ID_ESTACION)';
}
