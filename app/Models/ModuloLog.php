<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuloLog extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_LOG_MOD';
    protected $fillable = [
        'ID_LOG',
        'TIPO',
        'FECHA',
        'DETALLES',
        'COD_MEDICO'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = '(ID_LOG)';
}
