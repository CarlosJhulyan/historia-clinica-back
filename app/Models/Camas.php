<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camas extends Model
{
  protected $guarded = [];
  protected $table = "HCW_CAMAS";
  protected $fillable = [
    "CAMA_ID",
    "NUMERO",
    "TIPO",
    "PACIENTE",
    "ESTADO",
    "HABITACION_ID",
    "ESPECIALIDAD",
    "FECHA_INGRESO",
    "GENERO",
    "DIAS",
    "HABITACION_ID_ANTERIOR",
    "TRANSFERIDO",
    "FECHA_TRANSFERIDO",
    "HABITACION_ANTERIOR",
    "PISO_ANTERIOR",
    "HISTORIA_CLINICA",
  ];
  protected $casts = [];

  public $timestamps = false;
  protected $primaryKey = "CAMA_ID";
}
