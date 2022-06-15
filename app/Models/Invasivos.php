<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invasivos extends Model
{
    protected $guarded = [];
    protected $table = 'invasivos';
    protected $fillable = [
        'IDINVASIVOS',
        'FECHA_CVC',
        'FECHA_TET',
        'FECHA_VIA_PERIFERIA',
        'MOTIVO_VIA_PERIFERICA',
        'FECHA_SNG',
        'FECHA_FOLEY',
        'CODPACIENTE'
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'idInvasivos';
}
