<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sugerencia extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_SUGERENCIA';
    protected $fillable = [
        'codDiagnostico',
        'tipoDiagnostico',
        'nomDiagnostico',
        'cod_medico'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'codDiagnostico';
}
