<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horarios extends Model
{
	protected $guarded = [];
	protected $table = 'HCW_HORARIOS';
	protected $fillable = [
		'ID_HORARIO',
		'CMP',
		'NOMBRE_MEDICO',
		'FECHA',
		'HORA_INICIO',
		'HORA_FIN',
		'ESPECIALIDAD',
		'ID_ESPECIALIDAD',
	];

	protected $primaryKey = 'ID_HORARIO';
}
