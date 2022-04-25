<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $guarded = [];
    protected $table = 'MAE_MEDICO';
    protected $fillable = [
        'COD_MEDICO',
        'DES_NOM_MEDICO',
        'DES_APE_MEDICO'
    ];
    protected $casts = [
    ];

    public $timestamps = false;
    protected $primaryKey = 'COD_MEDICO';
}
