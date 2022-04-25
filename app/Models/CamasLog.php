<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamasLog extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_LOG_CAMAS';
    protected $fillable = [
        'ID_LOG_CAMAS',
        'TIPO',
        'FECHA',
        'DETALLES',
        'COD_MEDICO',
        'MODELO'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = '(ID_LOG_CAMAS)';
}
