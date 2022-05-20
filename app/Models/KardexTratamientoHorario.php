<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KardexTratamientoHorario extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_KARDEX_TRATAMIENTO_HORARIO';
    protected $fillable = [
        'ID',
        'ID_KARDEX_TRATAMIENTO',
        'HORA',
        'ADMINISTRADO',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
