<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pacientes extends Model
{
  protected $guarded = [];
  protected $table = "CME_PACIENTE";
  protected $fillable = [
    "COD_PACIENTE",
    "NOM_CLI",
    "APE_PAT_CLI",
    "APE_MAT_CLI",
    "SEXO_CLI",
  ];
  protected $casts = [];

  public $timestamps = false;
  protected $primaryKey = "COD_PACIENTE";
}
