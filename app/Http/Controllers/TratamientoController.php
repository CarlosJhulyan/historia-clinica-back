<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Antecedentes;
use App\Models\Estemat;
use App\Models\RecomendacionTratamiento;
use App\Models\Tratamiento;
use App\Oracle\OracleDB;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TratamientoController extends Controller
{
    // public function store(Request $request)
    // {
    //     $codGrupoCia = $request->input('codGrupoCia');
    //     $codPaciente = $request->input('codPaciente');
    //     $codMedico = $request->input('codMedico');
    //     $plan = $request->input('plan');
    //     $fecha = $request->input('fecha');
    //     $descripcion = $request->input('descripcion');
    //     $especialidad = $request->input('especialidad');
    //     $nombreMedico = $request->input('nombreMedico');
    //     $validator = Validator::make($request->all(), [
    //         'codGrupoCia' => 'required',
    //         'codPaciente' => 'required',
    //         'codMedico' => 'required',
    //         'plan' => 'required',
    //         'descripcion' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return CustomResponse::failure('Datos faltantes');
    //     } else {
    //         try {
    //             $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
    //             $registroTratamiento = [
    //                 'ID_DATOS_TRATAMIENTO' => $id,
    //                 'COD_PACIENTE' => $codPaciente,
    //                 'COD_GRUPO_CIA' => $codGrupoCia,
    //                 'COD_MEDICO' => $codMedico,
    //                 'FECHA' => $fecha,
    //                 'PLAN_TRATAMIENTO' => $plan,
    //                 'DESCRIPCION_TRATAMIENTO' => $descripcion,
    //                 'ESPECIALIDAD' => $especialidad,
    //                 'NOMBRE_MEDICO' => $nombreMedico
    //             ];
    //             Tratamiento::insert($registroTratamiento);
    //             return CustomResponse::success();
    //         } catch (Exception $e) {
    //             return CustomResponse::failure($e->getMessage());
    //         }
    //     }
    // }   

    /**
     * Obtener lista de antecedentes de tratamientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos/antecedentes",
     *     tags={"Tratamientos"},
     *     operationId="tratamientosAntecedentes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codMedico"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                     example="0000026144",
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
    public function getAntecedentes(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $codMedico = $request->input('codMedico');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'codMedico' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = Antecedentes::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])
                    ->orderBy('fecha', 'DESC')->limit(1)->get();
                if ($datos) {
                    return CustomResponse::success('Datos encontrados.', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta Antecedentes',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

/**
     * Obtener lista de tratamientos estomatologico
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos/estomatologico",
     *     tags={"Tratamientos"},
     *     operationId="tratamientosEstomatologico",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codMedico",
     *                  "nroAtencion"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                     example="0000026144",
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string",
     *                     example="0000347913",
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
    public function getEstomatologico(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $codMedico = $request->input('codMedico');

        $nroAtencion = $request->input('nroAtencion');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'codMedico' => 'required',
            'nroAtencion' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = Estemat::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $nroAtencion])
                    ->orderBy('fecha', 'DESC')->limit(1)->get();
                if ($datos) {
                    return CustomResponse::success('Datos encontrados.', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con Estomatologico',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener tratamientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos",
     *     tags={"Tratamientos"},
     *     operationId="tratamientos",
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
    public function tratamientos(Request $request)
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
                $stid = oci_parse($conn, "BEGIN :result :=  HHC_RECETA.F_LISTA_EMPRESA_PROD_RECETA2_D(cCodGrupoCia_in => :codgrupocia, cCodLocal_in => :codlocal); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $value) {
                            $datos = explode('Ã', $value);
                            // array_push(
                            //     $lista,
                            //     [
                            //         'key' => $datos[0] . '@' . $datos[22],
                            //         'COD_PROD' => $datos[0],
                            //         'DESC_PROD' => $datos[1],
                            //         'UNIDAD' => $datos[2],
                            //         'MARCA' => $datos[3],
                            //         'STK_FISICO' => $datos[4],
                            //         'VAL_FRAC' => $datos[8],
                            //         'GENERICO' => $datos[6],
                            //         'CADENA' => $datos[17],
                            //         'RUC' => $datos[22],
                            //         'NOM_LAB' => $datos[23],
                            //     ]
                            // );
                            array_push(
                                $lista,
                                [
                                    'key' => $datos[0] . '@' . $datos[23],
                                    'COD_PROD' => $datos[0],
                                    'IND_CALCULO_TRAT_HC' => $datos[1],
                                    'DESC_PROD' => $datos[2],
                                    'UNIDAD' => $datos[3],
                                    'MARCA' => $datos[4],
                                    'STK_FISICO' => $datos[5],
                                    'VAL_FRAC' => $datos[9],
                                    'GENERICO' => $datos[7],
                                    'CADENA' => $datos[18],
                                    'RUC' => $datos[23],
                                    'NOM_LAB' => $datos[24],
                                ]
                            );
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tratamientos encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener receta sugerida de tratamientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos/recetaSugerido",
     *     tags={"Tratamientos"},
     *     operationId="tratamientosRecetaSugerido",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codProdRuc"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codProdRuc",
     *                     type="string",
     *                     example="000023@20603070349",
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
    public function sugeridoReceta(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codProdRuc = $request->input('codProdRuc');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codProdRuc' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_RECETA.F_GET_PRODUCTOS_RECETA(cCodGrupoCia_in => :codgrupocia, cCodProd_in => :codProdRuc); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codProdRuc", $codProdRuc);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        $datos = explode('?', $row['RESULTADO']);
                        $result = [
                            'key' => $datos[0],
                            'COD_PROD' => $datos[0],
                            'DESC_PROD' => $datos[1],
                            'UNIDAD' => $datos[2],
                        ];
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tratamientos encontrados.', $result);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener recomendaciones de tratamientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos/getRecomendaciones",
     *     tags={"Tratamientos"},
     *     operationId="tratamientosGetRecomendaciones",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "nunReceta",
     *                  "atencionMedica",
     *                  "codProducto"
     *               },
     *                 @OA\Property(
     *                     property="nunReceta",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="atencionMedica",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codProducto",
     *                     type="string"
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
    function getRecomendaciones(Request $request)
    {
        $nunReceta = $request->input('nunReceta');
        $atencionMedica = $request->input('atencionMedica');
        $codProducto = $request->input('codProducto');
        $validator = Validator::make($request->all(), [
            'nunReceta' => 'required',
            'atencionMedica' => 'required',
            'codProducto' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {

                $resultado = RecomendacionTratamiento::where('NRO_RECETA', $nunReceta)
                    ->where('ATENCION_MEDICA', $atencionMedica)
                    ->where('COD_PROD', $codProducto)
                    ->first();

                return CustomResponse::success('Recomendaciones encontradas.', $resultado);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Guardar recomendaciones de tratamientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tratamientos/setRecomendaciones",
     *     tags={"Tratamientos"},
     *     operationId="tratamientosSetRecomendaciones",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "nunReceta",
     *                  "atencionMedica",
     *                  "codProducto",
     *                  "recomendacion"
     *               },
     *                 @OA\Property(
     *                     property="nunReceta",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="atencionMedica",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codProducto",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="recomendacion",
     *                     type="string"
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
    function setRecomendaciones(Request $request)
    {
        $nunReceta = $request->input('nunReceta');
        $atencionMedica = $request->input('atencionMedica');
        $codProducto = $request->input('codProducto');
        $recomendacion = $request->input('recomendacion');
        $validator = Validator::make($request->all(), [
            'nunReceta' => 'required',
            'atencionMedica' => 'required',
            'codProducto' => 'required',
            'recomendacion' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $resultado = RecomendacionTratamiento::where('NRO_RECETA', $nunReceta)
                    ->where('ATENCION_MEDICA', $atencionMedica)
                    ->where('COD_PROD', $codProducto)
                    ->first();

                if ($resultado) {
                    $resultado->RECOMENDACION = $recomendacion;
                    $resultado->save();
                } else {
                    $idRecomendacion = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                    $resultado = new RecomendacionTratamiento();
                    $resultado->ID_REC_TRAT = $idRecomendacion;
                    $resultado->NRO_RECETA = $nunReceta;
                    $resultado->ATENCION_MEDICA = $atencionMedica;
                    $resultado->COD_PROD = $codProducto;
                    $resultado->RECOMENDACION = $recomendacion;
                    $resultado->save();
                }

                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
}
