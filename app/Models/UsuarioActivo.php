<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioActivo extends Model
{

	protected $guarded = [];
	protected $table = 'HCW_USUARIO_ACTIVO';
	protected $fillable = [
		'USER_ID',
		'ULTIMA_CONEXION',
		'ESTADO'

	];
	protected $casts = [];

	public $timestamps = false;
	protected $primaryKey = 'USER_ID';
}
