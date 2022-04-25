<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DataTablaController extends Controller
{
    /**
     * Obtener la tabla de procedimientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tabla/procedimientos",
     *     tags={"Tablas"},
     *     operationId="tablaProcedimientos",
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
    public function getProcedimientosTabla(Request $request)
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
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_DATOS_HC_PROCEDIMIENTO(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
                            if ($datos[0] !== "" && $datos[1] !== "" && $datos[2] !== "" && $datos[3] !== "") {
                                array_push(
                                    $lista,
                                    [
                                        'key' => $datos[0],
                                        'COD_PROD' => $datos[0],
                                        'DESC_PROD' => $datos[1],
                                        'NOM_LAB' => $datos[2],
                                        'RUC' => $datos[3],
                                    ]
                                );
                            }
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tabla de Procedimientos encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener la tabla de imagenes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tabla/imagenes",
     *     tags={"Tablas"},
     *     operationId="tablaImagenes",
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
    public function getImagenesTabla(Request $request)
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
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_DATOS_HC_IMAGENES(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
                            if ($datos[0] !== "" && $datos[1] !== "" && $datos[2] !== "" && $datos[3] !== "") {
                                array_push(
                                    $lista,
                                    [
                                        'key' => $datos[0],
                                        'COD_PROD' => $datos[0],
                                        'DESC_PROD' => $datos[1],
                                        'NOM_LAB' => $datos[2],
                                        'RUC' => $datos[3],
                                    ]
                                );
                            }
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tabla de Imagenes encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener la tabla de laboratorio
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tabla/laboratorio",
     *     tags={"Tablas"},
     *     operationId="tablaLaboratorio",
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
    public function getLaboratorioTabla(Request $request)
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
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_DATOS_HC_LABORATORIO(:codgrupocia, :codLocal, :numAtendMed); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_bind_by_name($stid, ":numAtendMed", $numAtenMed);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
                            if ($datos[0] !== "" && $datos[1] !== "" && $datos[2] !== "" && $datos[3] !== "") {
                                array_push(
                                    $lista,
                                    [
                                        'key' => $datos[0],
                                        'COD_PROD' => $datos[0],
                                        'DESC_PROD' => $datos[1],
                                        'NOM_LAB' => $datos[2],
                                        'RUC' => $datos[3],
                                    ]
                                );
                            }
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tabla de Laboratorios encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }
}
