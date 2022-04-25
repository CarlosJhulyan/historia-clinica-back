<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class SelectController extends Controller
{
    /**
     * Obtener lista de maestro
     * 
     * @OA\Get(
     *     path="/historial-clinico-backend/public/api/combo/maestro",
     *     tags={"Listas"},
     *     operationId="comboMaestro",
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function maestro()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $cursor2 = oci_new_cursor($conn);
            $cursor3 = oci_new_cursor($conn);
            $cursor4 = oci_new_cursor($conn);
            $cursor5 = oci_new_cursor($conn);
            $cursor6 = oci_new_cursor($conn);
            $cursor7 = oci_new_cursor($conn);
            $cursor8 = oci_new_cursor($conn);
            $cursor9 = oci_new_cursor($conn);
            $cursor10 = oci_new_cursor($conn);
            $cursor11 = oci_new_cursor($conn);
            $cursor12 = oci_new_cursor($conn);
            $cursor13 = oci_new_cursor($conn);
            $curso = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor, ['codmaestro' => 47]);
            $tipoInformante = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor2, ['codmaestro' => 40]);
            $apetito = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor3, ['codmaestro' => 39]);
            $sed = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor4, ['codmaestro' => 39]);
            $sueno = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor5, ['codmaestro' => 39]);
            $orina = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor6, ['codmaestro' => 39]);
            $deposicion = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor7, ['codmaestro' => 39]);
            $estadoGeneral = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor8, ['codmaestro' => 38]);
            $tipoDiagnostico = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor9, ['codmaestro' => 37]);
            $viaAdministracion = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor10, ['codmaestro' => 36]);
            $prenatales = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor11, ['codmaestro' => 31]);
            $parto = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor12, ['codmaestro' => 32]);
            $inmunizaciones = OracleDB::executeFunctionCursor($conn, 'CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO', $cursor13, ['codmaestro' => 33]);
            oci_close($conn);
            $datos = [
                'curso' => $curso,
                'tipoInformante' => $tipoInformante,
                'apetito' => $apetito,
                'sed' => $sed,
                'sueno' => $sueno,
                'orina' => $orina,
                'deposicion' => $deposicion,
                'estadoGeneral' => $estadoGeneral,
                'tipoDiagnostico' => $tipoDiagnostico,
                'viaAdministracion' => $viaAdministracion,
                'prenatales' => $prenatales,
                'parto' => $parto,
                'inmunizaciones' => $inmunizaciones,
            ];
            return CustomResponse::success('Datos encontrados.', $datos);
        } catch (Exception $e) {
            return CustomResponse::failure($e->getMessage());
        }
    }

    /**
     * Obtener lista de procedimientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/combo/procedimientos",
     *     tags={"Listas"},
     *     operationId="comboProcedimientos",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal"
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getProcedimientos(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_LAB_ANTOMIA.F_LISTA_SERV_OTROS_D(:codgrupocia, :codLocal); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
                            array_push(
                                $lista,
                                [
                                    'key' => $datos[0],
                                    'COD_PROD' => $datos[0],
                                    'DESC_PROD' => $datos[1],
                                    'NOM_LAB' => $datos[2],
                                    'TIP_PROCESO' => $datos[3],
                                    'RUC' => $datos[4],
                                ]
                            );
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Procedimientos encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener lista de imagenes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/combo/imagenes",
     *     tags={"Listas"},
     *     operationId="comboImagenes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal"
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getImagenes(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_LAB_ANTOMIA.F_LISTA_SERV_IMAGENES(:codgrupocia, :codLocal); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
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
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Imagenes encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener lista de laboratorio
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/combo/laboratorio",
     *     tags={"Listas"},
     *     operationId="comboLaboratorio",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal"
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getLaboratorio(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_LAB_ANTOMIA.F_LISTA_SERV_LAB_ANATO(:codgrupocia, :codLocal); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codLocal", $codLocal);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
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
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Laboratorios encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }
}
