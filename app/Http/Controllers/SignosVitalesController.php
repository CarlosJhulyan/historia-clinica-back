<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\BalanceHidrico;
use App\Models\SignosVitales;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignosVitalesController extends Controller
{
    public function getRangeSignosVitales(Request $request)
    {
        $historia_clinica = $request->input('historiaClinica');
        $fechaI = $request->input('fechaI');
        $fechaF = $request->input('fechaF');

        $validator = Validator::make($request->all(), [
            'historiaClinica' => 'required',
            'fechaI' => 'required',
            'fechaF' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $signos = SignosVitales::select('*')
                    ->where(['HCW_SIGNOS_VITALES.HISTORIA_CLINICA' => $historia_clinica])
                    ->whereBetween('HCW_SIGNOS_VITALES.FECHA', [$fechaI, $fechaF])
                    ->get();
                $balance = BalanceHidrico::select('*')
                    ->where(['HCW_BALANCE_HIDRICO.HISTORIA_CLINICA' => $historia_clinica])
                    ->whereBetween('HCW_BALANCE_HIDRICO.FECHA', [$fechaI, $fechaF])
                    ->get();
                $data = [];
                $data[0]=$signos;
                $data[1]=$balance;
                // array_push($data, ...$signos);
                // array_push($data, ...$balance);
                return CustomResponse::success('Datos encontrados', $data);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    public function getOneSignosVitales(Request $request)
    {
        $historia_clinica = $request->input('historiaClinica');
        $fecha = $request->input('fecha');
        $turno = $request->input('turno');

        $validator = Validator::make($request->all(), [
            'historiaClinica' => 'required',
            'fecha' => 'required',
            'turno' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = SignosVitales::select('*')
                    ->where(['FECHA' => $fecha, 'HISTORIA_CLINICA' => $historia_clinica, 'TURNO' => $turno])
                    ->first();
                return CustomResponse::success('Datos encontrados', $datos);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    function createSignosVitales(Request $request)
    {
        $codMed = $request->input('codMedico');

        $paciente = $request->input("paciente");
        $historia_clinica = $request->input("historia_clinica");
        $fecha = $request->input("fecha");
        $turno = $request->input("turno");

        $RESPIRACION = $request->input("respiracion");
        $P_A = $request->input("p_a");
        $PULSO = $request->input("pulso");
        $TEPERATURA = $request->input("temperatura");

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "paciente" => "required",
            "historia_clinica" => "required",
            "fecha" => "required",
            "turno" => "required"
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $idSignos = round(microtime(true) * 1000) . "OH" . uniqid();

            SignosVitales::insert([
                "ID_SIGNOS_VITALES" => $idSignos,
                "PACIENTE" => $paciente,
                "HISTORIA_CLINICA" => $historia_clinica,
                "FECHA" => $fecha,
                "TURNO" => $turno,
                "RESPIRACION" => $RESPIRACION,
                "P_A" => $P_A,
                "PULSO" => $PULSO,
                "TEMPERATURA" => $TEPERATURA,
            ]);
            return CustomResponse::success("Signos Vitales Creados");
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function updateSignosVitales(Request $request)
    {
        $codMed = $request->input('codMedico');

        $id = $request->input("id");

        $RESPIRACION = $request->input("respiracion");
        $P_A = $request->input("p_a");
        $PULSO = $request->input("pulso");
        $TEPERATURA = $request->input("temperatura");

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'codMedico'       => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                SignosVitales::where('ID_SIGNOS_VITALES', $id)
                    ->update([
                        "RESPIRACION" => $RESPIRACION,
                        "P_A" => $P_A,
                        "PULSO" => $PULSO,
                        "TEMPERATURA" => $TEPERATURA,
                    ]);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
}
