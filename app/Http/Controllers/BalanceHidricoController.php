<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\BalanceHidrico;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceHidricoController extends Controller
{
    public function getHistoryBalanceHidrico(Request $request)
    {
        $historia_clinica = $request->input('historiaClinica');

        $validator = Validator::make($request->all(), [
            'historiaClinica' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $balance = BalanceHidrico::select('*')
                    ->where(['HCW_BALANCE_HIDRICO.HISTORIA_CLINICA' => $historia_clinica])
                    ->get();
                return CustomResponse::success('Datos encontrados', $balance);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    public function getOneBalanceHidrico(Request $request)
    {
        $historia_clinica = $request->input('historiaClinica');
        $fecha = $request->input('fecha');

        $validator = Validator::make($request->all(), [
            'historiaClinica' => 'required',
            'fecha' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = BalanceHidrico::select('*')
                    ->where(['FECHA' => $fecha, 'HISTORIA_CLINICA' => $historia_clinica])
                    ->first();
                return CustomResponse::success('Datos encontrados', $datos);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    function createBalanceHidrico(Request $request)
    {
        $codMed = $request->input('codMedico');

        $paciente = $request->input("paciente");
        $historia_clinica = $request->input("historia_clinica");
        $fecha = $request->input("fecha");
        $peso = $request->input("peso");
        $estacion = $request->input("estacion");

        $balance_hidrico = $request->input("balance_hidrico");

        $E_DIURESIS_0814 = $request->input("egreso_diuresis_0814");
        $E_DIURESIS_1420 = $request->input("egreso_diuresis_1420");
        $E_DIURESIS_2008 = $request->input("egreso_diuresis_2008");
        $E_DEPOSICION_0814 = $request->input("egreso_deposicion_0814");
        $E_DEPOSICION_1420 = $request->input("egreso_deposicion_1420");
        $E_DEPOSICION_2008 = $request->input("egreso_deposicion_2008");
        $E_TEMPERATURA_0814 = $request->input("egreso_temperatura_0814");
        $E_TEMPERATURA_1420 = $request->input("egreso_temperatura_1420");
        $E_TEMPERATURA_2008 = $request->input("egreso_temperatura_2008");
        $E_OPCIONAL = $request->input("egreso_opcional");
        $E_VALOR_0814 = $request->input("egreso_valor_0814");
        $E_VALOR_1420 = $request->input("egreso_valor_1420");
        $E_VALOR_2008 = $request->input("egreso_valor_2008");

        $I_ORAL_0814 = $request->input("ingreso_oral_0814");
        $I_ORAL_1420 = $request->input("ingreso_oral_1420");
        $I_ORAL_2008 = $request->input("ingreso_oral_2008");
        $I_PARENTAL_0814 = $request->input("ingreso_parental_0814");
        $I_PARENTAL_1420 = $request->input("ingreso_parental_1420");
        $I_PARENTAL_2008 = $request->input("ingreso_parental_2008");
        $I_TRATAMIENTO_0814 = $request->input("ingreso_tratamiento_0814");
        $I_TRATAMIENTO_1420 = $request->input("ingreso_tratamiento_1420");
        $I_TRATAMIENTO_2008 = $request->input("ingreso_tratamiento_2008");
        $I_OPCIONAL = $request->input("ingreso_opcional");
        $I_VALOR_0814 = $request->input("ingreso_valor_0814");
        $I_VALOR_1420 = $request->input("ingreso_valor_1420");
        $I_VALOR_2008 = $request->input("ingreso_valor_2008");

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "paciente" => "required",
            "historia_clinica" => "required",
            "fecha" => "required",
            "peso" => "required",
            "estacion" => "required"
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $idBalance = round(microtime(true) * 1000) . "OH" . uniqid();

            BalanceHidrico::insert([
                "ID_BALANCE_HIDRICO" => $idBalance,
                "PACIENTE" => $paciente,
                "HISTORIA_CLINICA" => $historia_clinica,
                "FECHA" => $fecha,
                "PESO" => $peso,
                "ESTACION" => $estacion,
                "BALANCE_HIDRICO" => $balance_hidrico,
                "E_DIURESIS_0814" => $E_DIURESIS_0814,
                "E_DIURESIS_1420" => $E_DIURESIS_1420,
                "E_DIURESIS_2008" => $E_DIURESIS_2008,
                "E_DEPOSICION_0814" => $E_DEPOSICION_0814,
                "E_DEPOSICION_1420" => $E_DEPOSICION_1420,
                "E_DEPOSICION_2008" => $E_DEPOSICION_2008,
                "E_TEMPERATURA_0814" => $E_TEMPERATURA_0814,
                "E_TEMPERATURA_1420" => $E_TEMPERATURA_1420,
                "E_TEMPERATURA_2008" => $E_TEMPERATURA_2008,
                "E_OPCIONAL" => $E_OPCIONAL,
                "E_VALOR_0814" => $E_VALOR_0814,
                "E_VALOR_1420" => $E_VALOR_1420,
                "E_VALOR_2008" => $E_VALOR_2008,
                "I_ORAL_0814" => $I_ORAL_0814,
                "I_ORAL_1420" => $I_ORAL_1420,
                "I_ORAL_2008" => $I_ORAL_2008,
                "I_PARENTAL_0814" => $I_PARENTAL_0814,
                "I_PARENTAL_1420" => $I_PARENTAL_1420,
                "I_PARENTAL_2008" => $I_PARENTAL_2008,
                "I_TRATAMIENTO_0814" => $I_TRATAMIENTO_0814,
                "I_TRATAMIENTO_1420" => $I_TRATAMIENTO_1420,
                "I_TRATAMIENTO_2008" => $I_TRATAMIENTO_2008,
                "I_OPCIONAL" => $I_OPCIONAL,
                "I_VALOR_0814" => $I_VALOR_0814,
                "I_VALOR_1420" => $I_VALOR_1420,
                "I_VALOR_2008" => $I_VALOR_2008,
            ]);
            return CustomResponse::success("Balance Hidrico Creado");
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function updateBalanceHidrico(Request $request)
    {
        $codMed = $request->input('codMedico');

        $id = $request->input('id');
        $peso = $request->input("peso");
        $estacion = $request->input("estacion");

        $balance_hidrico = $request->input("balance_hidrico");

        $E_DIURESIS_0814 = $request->input("egreso_diuresis_0814");
        $E_DIURESIS_1420 = $request->input("egreso_diuresis_1420");
        $E_DIURESIS_2008 = $request->input("egreso_diuresis_2008");
        $E_DEPOSICION_0814 = $request->input("egreso_deposicion_0814");
        $E_DEPOSICION_1420 = $request->input("egreso_deposicion_1420");
        $E_DEPOSICION_2008 = $request->input("egreso_deposicion_2008");
        $E_TEMPERATURA_0814 = $request->input("egreso_temperatura_0814");
        $E_TEMPERATURA_1420 = $request->input("egreso_temperatura_1420");
        $E_TEMPERATURA_2008 = $request->input("egreso_temperatura_2008");
        $E_OPCIONAL = $request->input("egreso_opcional");
        $E_VALOR_0814 = $request->input("egreso_valor_0814");
        $E_VALOR_1420 = $request->input("egreso_valor_1420");
        $E_VALOR_2008 = $request->input("egreso_valor_2008");

        $I_ORAL_0814 = $request->input("ingreso_oral_0814");
        $I_ORAL_1420 = $request->input("ingreso_oral_1420");
        $I_ORAL_2008 = $request->input("ingreso_oral_2008");
        $I_PARENTAL_0814 = $request->input("ingreso_parental_0814");
        $I_PARENTAL_1420 = $request->input("ingreso_parental_1420");
        $I_PARENTAL_2008 = $request->input("ingreso_parental_2008");
        $I_TRATAMIENTO_0814 = $request->input("ingreso_tratamiento_0814");
        $I_TRATAMIENTO_1420 = $request->input("ingreso_tratamiento_1420");
        $I_TRATAMIENTO_2008 = $request->input("ingreso_tratamiento_2008");
        $I_OPCIONAL = $request->input("ingreso_opcional");
        $I_VALOR_0814 = $request->input("ingreso_valor_0814");
        $I_VALOR_1420 = $request->input("ingreso_valor_1420");
        $I_VALOR_2008 = $request->input("ingreso_valor_2008");

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'codMedico'       => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                BalanceHidrico::where('ID_BALANCE_HIDRICO', $id)
                    ->update([
                        "PESO" => $peso,
                        "ESTACION" => $estacion,
                        "BALANCE_HIDRICO" => $balance_hidrico,
                        "E_DIURESIS_0814" => $E_DIURESIS_0814,
                        "E_DIURESIS_1420" => $E_DIURESIS_1420,
                        "E_DIURESIS_2008" => $E_DIURESIS_2008,
                        "E_DEPOSICION_0814" => $E_DEPOSICION_0814,
                        "E_DEPOSICION_1420" => $E_DEPOSICION_1420,
                        "E_DEPOSICION_2008" => $E_DEPOSICION_2008,
                        "E_TEMPERATURA_0814" => $E_TEMPERATURA_0814,
                        "E_TEMPERATURA_1420" => $E_TEMPERATURA_1420,
                        "E_TEMPERATURA_2008" => $E_TEMPERATURA_2008,
                        "E_OPCIONAL" => $E_OPCIONAL,
                        "E_VALOR_0814" => $E_VALOR_0814,
                        "E_VALOR_1420" => $E_VALOR_1420,
                        "E_VALOR_2008" => $E_VALOR_2008,
                        "I_ORAL_0814" => $I_ORAL_0814,
                        "I_ORAL_1420" => $I_ORAL_1420,
                        "I_ORAL_2008" => $I_ORAL_2008,
                        "I_PARENTAL_0814" => $I_PARENTAL_0814,
                        "I_PARENTAL_1420" => $I_PARENTAL_1420,
                        "I_PARENTAL_2008" => $I_PARENTAL_2008,
                        "I_TRATAMIENTO_0814" => $I_TRATAMIENTO_0814,
                        "I_TRATAMIENTO_1420" => $I_TRATAMIENTO_1420,
                        "I_TRATAMIENTO_2008" => $I_TRATAMIENTO_2008,
                        "I_OPCIONAL" => $I_OPCIONAL,
                        "I_VALOR_0814" => $I_VALOR_0814,
                        "I_VALOR_1420" => $I_VALOR_1420,
                        "I_VALOR_2008" => $I_VALOR_2008,
                    ]);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
}
