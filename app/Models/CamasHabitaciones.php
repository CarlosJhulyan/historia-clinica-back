<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamasHabitaciones extends Model
{
	protected $guarded = [];
	protected $table = 'HCW_CAMAS_HABITACIONES';
	protected $fillable = [
		'HABITACION_ID',
		'NOMBRE_HABITACION',
		'PISO_ID'
	];
	protected $casts = [];

	public $timestamps = false;
	protected $primaryKey = 'HABITACION_ID';
}
