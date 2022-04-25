<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\ReporteAuditoria;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use PHPUnit\Util\Json;

class ReportController extends Controller
{
    public function getAuditoriaEspecialidades(Request $request)
    {
        $especialidades = $request->input('ESPECIALIDAD');
        $fechaInicio = $request->input('FECHA_INICIO');
        $fechaFin = $request->input('FECHA_FIN');
        $cod_medico = $request->input('COD_MEDICO');
        $todos = $request->input('TODOS');

        $validator = Validator::make($request->all(), [
            'FECHA_INICIO' => 'required',
            'FECHA_FIN' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            if ($especialidades) {
                if ($cod_medico) {
                    $resAuditoria = ReporteAuditoria::select('id as key', 'especialidad', 'puntaje', 'cod_medico', 'nom_medico', 'fecha')
                        ->whereIn('ESPECIALIDAD', $especialidades)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->where('COD_MEDICO', $cod_medico)
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                } else {
                    $resAuditoria = ReporteAuditoria::select('id as key', 'especialidad', 'puntaje', 'cod_medico', 'nom_medico', 'fecha')
                        ->whereIn('ESPECIALIDAD', $especialidades)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                }
            } else if ($cod_medico) {
                $resAuditoria = ReporteAuditoria::select('id as key', 'especialidad', 'puntaje', 'cod_medico', 'nom_medico', 'fecha')
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->where('COD_MEDICO', $cod_medico)
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            }
            else {
                $resAuditoria = ReporteAuditoria::select('id as key', 'especialidad', 'puntaje', 'cod_medico', 'nom_medico', 'fecha')
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            }

            if ($todos) {
                $resAuditoria = ReporteAuditoria::select('id as key', 'especialidad', 'puntaje', 'cod_medico', 'nom_medico', 'fecha')
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            }
            $estrellas = DB::table('HCW_AUD_ESTRELLAS')->get();
            //Asignar estrellas segun puntaje
            foreach ($resAuditoria as $key => $value) {
                foreach ($estrellas as $key2 => $value2) {
                    if ($value->puntaje >= $value2->min && $value->puntaje <= $value2->max) {
                        $resAuditoria[$key]->estrellas = $value2->cantidad;
                    }
                }
            }
            $response = [];
            foreach ($resAuditoria as $key => $value) {
                //Agrupar los datos por especialidad
                if (!isset($response[$value->especialidad])) {
                    $response[$value->especialidad] = [];
                    array_push($response[$value->especialidad], $value);
                } else {
                    array_push($response[$value->especialidad], $value);
                }
            }
            return CustomResponse::success('Datos obtenidos', $response);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error en los servidores');
        }
    }

    public function getAuditoria(Request $request)
    {
        $especialidades = $request->input('ESPECIALIDAD');
        $codMedico = $request->input('COD_MEDICO');
        $fechaInicio = $request->input('FECHA_INICIO');
        $fechaFin = $request->input('FECHA_FIN');

        $validator = Validator::make($request->all(), [
            'FECHA_INICIO' => 'required',
            'FECHA_FIN' => 'required'

        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            if ($especialidades) {
                if ($codMedico) {
                    $resAuditoria = ReporteAuditoria::select('*')
                        ->join('HCW_REP_AUDITORIA_V1', 'id', '=', 'rep_auditoria')
                        ->whereIn('ESPECIALIDAD', $especialidades)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->where('COD_MEDICO', $codMedico)
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                } else {
                    $resAuditoria = ReporteAuditoria::select('*')
                        ->join('HCW_REP_AUDITORIA_V1', 'id', '=', 'rep_auditoria')
                        ->whereIn('ESPECIALIDAD', $especialidades)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                }
            } else if ($codMedico) {
                $resAuditoria = ReporteAuditoria::select('*')
                    ->join('HCW_REP_AUDITORIA_V1', 'id', '=', 'rep_auditoria')
                    ->where('COD_MEDICO', $codMedico)
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            } else {
                $resAuditoria = ReporteAuditoria::select('*')
                    ->join('HCW_REP_AUDITORIA_V1', 'id', '=', 'rep_auditoria')
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            }

            return CustomResponse::success('Datos obtenidos', $resAuditoria);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    public function getEspecialidades(Request $request)
    {

        $especialidad = $request->input('especialidad');

        $validator = Validator::make($request->all(), [
            'especialidad' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $resAuditoria = ReporteAuditoria::select('ESPECIALIDAD', 'ID as KEY')
                // ->where('ESPECIALIDAD', 'like', '%' . $especialidad . '%')
                ->orderBy('ESPECIALIDAD', 'DESC')
                ->get();
            return CustomResponse::success('Datos obtenidos', $resAuditoria);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    public function obtenerPesoEspecialidades() {
        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, "select * from HCW_PESO_ESPECIALIDAD");
            oci_execute($stid);
            $lista = [];
            while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                array_push($lista, $row);
            }
            oci_close($conn);
            return CustomResponse::success('Datos obtenidos', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Ocurrió un error en los servidores');
        }
    }
}
