<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosFirmas extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_FIRMAS';
    protected $fillable = [
        'COD_MED',
        'URL_FIRMA',
        'FECHA_FIRMAS',
        'ESTADO'
    ];
}
