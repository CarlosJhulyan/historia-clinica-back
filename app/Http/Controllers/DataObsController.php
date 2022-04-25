<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DataObsController extends Controller
{
    /**
     * Obtener la observación de un tratamiento
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/obs/tratamiento",
     *     tags={"Observaciones"},
     *     operationId="tratamiento",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string",
     *                     example="0000384457",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getObsTratamiento(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numAtenMed = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numAtendMed' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_GET_OBS_TRATAMIENTO(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $resultadoObsTratamiento, 4000);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_close($conn);
                return CustomResponse::success('Obs de Tratamientos encontrados.', $resultadoObsTratamiento);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener la observación de un procedimiento
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/obs/procedimiento",
     *     tags={"Observaciones"},
     *     operationId="procedimiento",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string",
     *                     example="0000384457",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getObsProcedimiento(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numAtenMed = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numAtendMed' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_GET_OBS_PROCEDIMIENTO(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $resultadoObsProcedimiento, 4000);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_close($conn);
                return CustomResponse::success('Obs de Procedimientos encontrados.', $resultadoObsProcedimiento);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener la observación de imagenes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/obs/imagenes",
     *     tags={"Observaciones"},
     *     operationId="imagenes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string",
     *                     example="0000384457",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getObsImagenes(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numAtenMed = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numAtendMed' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_GET_OBS_IMAGENES(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $resultadoObsImg, 4000);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_close($conn);
                return CustomResponse::success('Obs de Imagenes encontrados.', $resultadoObsImg);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener la observación del laboratorio
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/obs/laboratorio",
     *     tags={"Observaciones"},
     *     operationId="laboratorio",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string",
     *                     example="0000384457",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getObsLaboratorio(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numAtenMed = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numAtendMed' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_GET_OBS_LABORATORIO(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $resultadoObsLab, 4000);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_close($conn);
                return CustomResponse::success('Obs de Laboratorios encontrados.', $resultadoObsLab);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }
}
