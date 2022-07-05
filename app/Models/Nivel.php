<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{

    protected $guarded = [];
    protected $table = 'NIVEL';
    protected $fillable = [
        'ID_NIVEL',
        'ID_PADRE',
        'DESCRIPCION',
        'MODULO',
        'FECHA_CREACION',
        'FECHA_ACTUALIZACION',
        'ESTADO'

    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_NIVEL';
}