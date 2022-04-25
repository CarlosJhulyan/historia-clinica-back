<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
	protected $guarded = [];
	protected $table = 'HCW_FIRMAS';
	protected $fillable = [
		'COD_MED',
		'URL_FIRMA',
		'FECHA_FIRMA',
		'ESTADO'
	];
	protected $casts = [];

	public $timestamps = false;
	protected $primaryKey = 'COD_MED';
}
