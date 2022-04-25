<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class ReportesController extends Controller
{
    // ------------------ TABLAS PRIMARIAS -----------------------

    public function getTablasPrimarias(Request $request)
    {
        try {
            // DatosOdontograma::where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])->delete();
            $especialidades = DB::select('SELECT * FROM RES_ESP_DATA');
            $meses = DB::select('SELECT * FROM RES_MES_DATA');
            $tipos = DB::select('SELECT * FROM RES_TIPO_DATA');
            return CustomResponse::success('Datos obtenidos', [$especialidades, $meses, $tipos]);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    // ------------------ REPORTE 01 -----------------------

    public function getReporte1(Request $request)
    {
        $año = $request->input('AÑO');

        $validator = Validator::make($request->all(), [
            'AÑO' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            // DatosOdontograma::where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])->delete();
            $resOrdenes = DB::select('SELECT * FROM RES_ANUAL_MES_ORDENES WHERE AÑO= ?', [$año]);
            $resProductos = DB::select('SELECT * FROM RES_ANUAL_MES_PRODUCTOS WHERE AÑO= ?', [$año]);
            return CustomResponse::success('Datos obtenidos', [$resOrdenes, $resProductos]);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    // ------------------ REPORTE 02 -----------------------

    public function getReporte2(Request $request)
    {
        $año = $request->input('AÑO');

        $validator = Validator::make($request->all(), [
            'AÑO' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $resVentas = DB::select('SELECT * FROM RES_ANUAL_MES_VENTAS WHERE AÑO= ?', [$año]);

            return CustomResponse::success('Datos obtenidos', $resVentas);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }


    // ------------------ REPORTE 03 -----------------------

    public function getReporte3(Request $request)
    {
        $año = $request->input('AÑO');
        $mes = $request->input('MES');
        $especialidad = $request->input('ESPECIALIDAD');

        $validator = Validator::make($request->all(), [
            'AÑO' => 'required',
            'MES' => 'required',
            'ESPECIALIDAD' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $data = DB::select('SELECT * FROM RES_TOP_ESPECIALIDAD_MES_VENTA WHERE AÑO= ? AND COD_ESPECIALIDAD=? AND COD_MES =?  ORDER BY POSICION_TOP ASC', [$año, $especialidad, $mes]);

            return CustomResponse::success('Datos obtenidos', $data);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    // ------------------ REPORTE 04 -----------------------

    public function getReporte4(Request $request)
    {
        $año = $request->input('AÑO');

        $validator = Validator::make($request->all(), [
            'AÑO' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $resVentas = DB::select('SELECT * FROM RES_ANUAL_MES_ESPECIALIDAD WHERE AÑO= ?', [$año]);

            return CustomResponse::success('Datos obtenidos', $resVentas);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }

    public function getReporte4Detalle(Request $request)
    {
        $año = $request->input('AÑO');
        $mes = $request->input('MES');
        $especialidad = $request->input('ESPECIALIDAD');

        $validator = Validator::make($request->all(), [
            'AÑO' => 'required',
            'MES' => 'required',
            'ESPECIALIDAD' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $data = DB::select('SELECT * FROM RES_ANUAL_MES_ESPECIALIDAD_DET WHERE AÑO= ? AND CODIGO_ESPECIALIDAD=? AND COD_MES =? ORDER BY NOMBRE_PRODUCTO ASC', [$año, $especialidad, $mes]);

            return CustomResponse::success('Datos obtenidos', $data);
        } catch (Exception $e) {
            error_log($e);
            return CustomResponse::failure('Ocurrió un error');
        }
    }
}
