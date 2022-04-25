<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Rol extends Model
{
	protected $guarded = [];
	protected $table = 'HCW_USU_MOD';
	protected $fillable = [
		'ID_DATOS',
		'COD_MOD',
		'COD_MEDICO'

	];
	protected $casts = [];

	public $timestamps = false;
	protected $primaryKey = 'ID_DATOS';
}
