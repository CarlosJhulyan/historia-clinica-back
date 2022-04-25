<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CamasPisos extends Model
{
	protected $guarded = [];
	protected $table = 'HCW_CAMAS_PISOS';
	protected $fillable = [
			'PISO_ID',
			'NOMBRE_PISO'
	];
	protected $casts = [];

	public $timestamps = false;
	protected $primaryKey = 'PISO_ID';
}
