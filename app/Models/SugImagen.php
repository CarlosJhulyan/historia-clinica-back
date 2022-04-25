<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SugImagen extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_SUG_IMG';
    protected $fillable = [
        'key',
        'COD_PROD',
        'DESC_PROD',
        'NOM_LAB',
        'RUC'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'key';
}
