<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreTriaje extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_PRE_TRIAJE';
    protected $fillable = [
        'COD_PACIENTE',
        'COD_USU_CREA',
        'PACIENTE',
        'FEC_TOMA'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID';
}
