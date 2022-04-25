<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Camas;
use App\Models\CamasHabitaciones;
use App\Models\CamasLog;
use App\Models\CamasPisos;
use App\Models\Estacion;
use App\Models\Pacientes;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstacionController extends Controller
{
    function getEstaciones()
    {
        try {
            $model = Estacion::select("*")
                ->get();
            return CustomResponse::success("Estaciones", $model);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }
}
