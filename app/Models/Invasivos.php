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
        'CODPACIENTE',
        'MOTIVO_CVC',
        'MOTIVO_TET',
        'MOTIVO_FOLEY',
        'MOTIVO_SNG',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'idInvasivos';

    public function scopeFechaCVC($query, $fecha1cvc, $fecha2cvc)
	{
		if ($fecha1cvc && $fecha2cvc)
			return $query->whereBetween('FECHA_CVC', [$fecha1cvc,$fecha2cvc]);
	}

    public function scopeFechaTET($query, $fecha1tet, $fecha2tet)
	{
		if ($fecha1tet && $fecha2tet)
			return $query->whereBetween('FECHA_TET', [$fecha1tet,$fecha2tet]);
	}
    public function scopeFechaSNG($query, $fecha1sng, $fecha2sng)
	{
		if ($fecha1sng && $fecha2sng)
			return $query->whereBetween('FECHA_SNG', [$fecha1sng,$fecha2sng]);
	}
    public function scopeFechaFOLEY($query, $fecha1f, $fecha2f)
	{
		if ($fecha1f && $fecha2f)
			return $query->whereBetween('FECHA_FOLEY', [$fecha1f,$fecha2f]);
	}
    public function scopeFechaVIA($query, $fecha1via, $fecha2via)
	{
		if ($fecha1via && $fecha2via)
			return $query->whereBetween('FECHA_VIA_PERIFERIA', [$fecha1via,$fecha2via]);
	}
}
