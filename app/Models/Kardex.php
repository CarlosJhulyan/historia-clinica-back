<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_KARDEX';
    protected $fillable = [
        'ID',
        'COD_MEDICO',
        'NOM_MEDICO',
        'COD_PACIENTE',
        'NOM_PACIENTE',
        'HC',
        'FECHA',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
