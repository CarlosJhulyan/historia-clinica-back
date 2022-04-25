<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Patologico;
use App\Models\Antecedentes;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;
use DateTime;

class AntecedentesController extends Controller
{

    /**
     * Obtener la historia clinica
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/hc",
     *     tags={"Antecedentes"},
     *     operationId="hc",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "codPaciente",
     *                  "codSecuencia"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codSecuencia",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codLocal": "001",
     *                  "codPaciente": "0010228664",
     *                  "codSecuencia": "4"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function antecedentesHC(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codPaciente = $request->input('codPaciente');
        $codSecuencia = $request->input('codSecuencia');



        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codPaciente' => 'required',
            'codSecuencia' => 'required',

        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);

                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_OBTENER_ANTECEDENTE_HC(cCodGrupoCia_in => :codgrupocia, cCodLocal_in => :codlocal, cCodPaciente_in => :codpaciente, cSecuenciaHC_in => :codsecuencia); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":codpaciente", $codPaciente);
                oci_bind_by_name($stid, ":codsecuencia", $codSecuencia);

                oci_execute($stid);
                oci_execute($cursor);
                if ($stid) {
                    $lista = [];
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        foreach ($row as $key => $datos) {
                            $lista[$key] = $datos;
                        }
                    }
                    return CustomResponse::success('Antecedentes encontrados.', $lista);
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener los antecedentes generales
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/generales",
     *     tags={"Antecedentes"},
     *     operationId="generales",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codMaestro"
     *               },
     *                 @OA\Property(
     *                     property="codMaestro",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codMaestro": "34"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function antecedentesGenerales(Request $request)
    {
        $codMaestro = $request->input('codMaestro');
        $validator = Validator::make($request->all(), [
            'codMaestro' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_LST_CMB_CHECK_MAESTRO(:codmaestro); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codmaestro", $codMaestro);
                oci_execute($stid);
                oci_execute($cursor);
                if ($stid) {
                    $lista = [];
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push(
                            $lista,
                            [
                                'CODIGO' => $row['CODIGO'],
                                'ETIQUETA' => $row['ETIQUETA'],
                                'OPCION_OTRO' => $row['OPCION_OTRO']
                            ]
                        );
                    }
                    return CustomResponse::success('Antecedentes generales encontrados.', $lista);
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener los diagnosticos en antecedentes (trae mas de 20 mil registros)
     * 
     * @OA\Get(
     *     path="/historial-clinico-backend/public/api/antecedentes/diagnosticos",
     *     tags={"Antecedentes"},
     *     operationId="diagnosticos",
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function listarDiagnostico()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_LISTAR_DIAGNOSTICO; END;");
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);
            if ($stid) {
                $lista = [];
                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    array_push(
                        $lista,
                        [
                            'COD_CIE_10' => $row['COD_CIE_10'],
                            'DES_DIAGNOSTICO' => $row['DES_DIAGNOSTICO'],
                            'COD_DIAGNOSTICO' => $row['COD_DIAGNOSTICO']
                        ]
                    );
                }
                return CustomResponse::success('Antecedentes generales encontrados.', $lista);
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);
        } catch (Exception $e) {
            return CustomResponse::failure($e->getMessage());
        }
    }

    /**
     * Obtener las funciones vitales de antecedentes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/funcionesvitales",
     *     tags={"Antecedentes"},
     *     operationId="funcionesVitales",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codCia",
     *                  "codLocal",
     *                  "nroAtencion"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codCia": "001",
     *                  "codLocal": "001",
     *                  "nroAtencion": "0000347913"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function funcionesVitales(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codCia = $request->input('codCia');
        $codLocal = $request->input('codLocal');
        $nroAtencion = $request->input('nroAtencion');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codCia' => 'required',
            'codLocal' => 'required',
            'nroAtencion' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_DFLORES(:codgrupocia, :codcia,:codlocal,:nroatencion); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codcia", $codCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":nroatencion", $nroAtencion);
                oci_execute($stid);
                oci_execute($cursor);
                if ($stid) {
                    $uuu = [];
                    while (($row = oci_fetch_object($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push($uuu, $row);
                        // $data = $row;
                    }

                    return CustomResponse::success('Antecedentes generales encontrados.', $uuu);
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener los síntomas patológicos de antecedentes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/getPatologico",
     *     tags={"Antecedentes"},
     *     operationId="getPatologico",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codSecuencia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="tipo",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codLocal": "001",
     *                  "codPaciente": "0010185756",
     *                  "codSecuencia": "1",
     *                  "tipo": "PA"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function listarPatologicos(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $codLocal = $request->input('codLocal');
        $codSecuencia = $request->input('codSecuencia');
        $tipo = $request->input('tipo');


        try {
            $data = Patologico::query()
                ->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_LOCAL' => $codLocal,
                    'COD_PACIENTE' => $codPaciente,
                    'SEC_HC_ANTECEDENTES' => $codSecuencia,
                    'IND_TIPO' => $tipo,
                ])->get();
            foreach ($data as $key => $value) {
                $codDiagnostico = $data[$key]['cod_diagnostico'];
                $diagnostico = DB::select('SELECT * FROM MAE_DIAGNOSTICO WHERE COD_DIAGNOSTICO=?', [$codDiagnostico]);
                $data[$key]['cod_cie_10'] = $diagnostico[0]->cod_cie_10;
                $data[$key]['des_diagnostico'] = $diagnostico[0]->des_diagnostico;
            }
            return response()->json([
                'success' => true,
                'message' => 'Datos encontrados.',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return CustomResponse::failure($e->getMessage());
        }
    }

    /**
     * Obtener los paneles de antecedentes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/paneles",
     *     tags={"Antecedentes"},
     *     operationId="paneles",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codLocal",
     *                  "codSecuencia"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codSecuencia",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codLocal": "001",
     *                  "codPaciente": "0010185756",
     *                  "codSecuencia": "1"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getPanelesAntecedentes(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codPaciente = $request->input('codPaciente');
        $codSecuencia = $request->input('codSecuencia');


        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codPaciente' => 'required',
            'codSecuencia' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_OBTENER_ANTECEDENTE_HC(cCodGrupoCia_in => :codgrupocia, cCodLocal_in => :codlocal, cCodPaciente_in => :codpaciente, cSecuenciaHC_in => :codsecuencia); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":codpaciente", $codPaciente);
                oci_bind_by_name($stid, ":codsecuencia", $codSecuencia);

                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                if ($stid) {
                    while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push($lista, $row);
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
                return CustomResponse::success('Tabla de Paneles encontrados.', $lista);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Guardar los antecedentes (esta funcion graba un registro en una consulta - usar con cuidado)
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/setAntecedentes",
     *     tags={"Antecedentes"},
     *     operationId="setAntecedentes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codLocal",
     *                  "codMedico",
     *                  "codPaciente",
     *                  "secuenciaHC"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="secuenciaHC",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="generales",
     *                     type="object",
     *                      @OA\Property(
     *                          property="medicamentos",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ram",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ocupacionales",
     *                          type="string"
     *                      )
     *                 ),
     *                 @OA\Property(
     *                     property="antecedentes",
     *                     type="object",
     *                     @OA\Property(
     *                          property="fecha",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="diabetes",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="fiebre_reumatica",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="enfermedad_hepaticas",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="hemorragias",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="tuberculosis",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="enfermedad_cardiovascular",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="reaccion_anormal_local",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="alergia_penecilina",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="anemia",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="enfermedad_renal",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="reaccion_anormal_drogas",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="otras",
     *                          type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="fisiologicos",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="ginecologicos",
     *                     type="object",
     *                     @OA\Property(
     *                          property="edadMenarquia",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="rcMenstruacion",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="cicloMenstruacion",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="fechaFur",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="fechaFpp",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="rs",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="disminorrea",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="nroGestaciones",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="paridad",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="fechaFup",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="nroCesareas",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="pap",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="mamografia",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="mac",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="otros",
     *                          type="string"
     *                     ),
     *                     @OA\Property(
     *                          property="indReglaRegular",
     *                          type="string"
     *                     ),
     *                 ),
     *                 @OA\Property(
     *                     property="patologicos",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="patologicosFamiliares",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codLocal": "001",
     *                  "codMedico": "0010185756",
     *                  "codPaciente": "0010185756",
     *                  "secuenciaHC": "1",
     *                  "generales": {
     *                      "medicamentos":"",
     *                      "ram":"",
     *                      "ocupacionales":""
     *                  },
     *                  "antecedentes": {
     *                      "fecha":"",
     *                      "diabetes":"",
     *                      "fiebre_reumatica":"",
     *                      "enfermedad_hepaticas":"",
     *                      "hemorragias":"",
     *                      "tuberculosis":"",
     *                      "enfermedad_cardiovascular":"",
     *                      "reaccion_anormal_local":"",
     *                      "alergia_penecilina":"",
     *                      "anemia":"",
     *                      "enfermedad_renal":"",
     *                      "reaccion_anormal_drogas":"",
     *                      "otras":""
     *                  },
     *                  "fisiologicos": "",
     *                  "ginecologicos": {
     *                      "edadMenarquia":"",
     *                      "rcMenstruacion":"",
     *                      "cicloMenstruacion":"",
     *                      "fechaFur":"",
     *                      "fechaFpp":"",
     *                      "rs":"",
     *                      "disminorrea":"",
     *                      "nroGestaciones":"",
     *                      "paridad":"",
     *                      "fechaFup":"",
     *                      "nroCesareas":"",
     *                      "pap":"",
     *                      "mamografia":"",
     *                      "mac":"",
     *                      "otros":"",
     *                      "indReglaRegular":""
     *                  },
     *                  "patologicos": "",
     *                  "patologicosFamiliares": ""
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function guardarAntecedentes(Request $request)
    {
        $codGrupoCia =  $request->input('codGrupoCia');
        $codLocal =  $request->input('codLocal');
        $codPaciente =  $request->input('codPaciente');
        $codMedico =  $request->input('codMedico');
        $secuenciaHC =  $request->input('secuenciaHC');
        $generales = $request->input('generales');
        $antecedentes = $request->input('antecedentes');
        $fisiologicos = $request->input('fisiologicos');
        $ginecologicos = $request->input('ginecologicos');
        $patologicos = $request->input('patologicos');
        $patologicosF = $request->input('patologicosFamiliares');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codMedico' => 'required',
            'codPaciente' => 'required',
            'secuenciaHC' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            $conn = OracleDB::getConnection();

            // GRABAR GENERALES

            try {
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTECEDENTES_HC1( :codgrupocia, 
        :codlocal, :codpaciente, :sechcantecedentes, :medicamentos, :ram, :ocupacionales, :codmedico, :usucrea); END;");
                $secuencia = '';
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":codpaciente", $codPaciente);
                oci_bind_by_name($stid, ":sechcantecedentes", $secuencia);
                oci_bind_by_name($stid, ":medicamentos", $generales['medicamentos']);
                oci_bind_by_name($stid, ":ram", $generales['ram']);
                oci_bind_by_name($stid, ":ocupacionales", $generales['ocupacionales']);
                oci_bind_by_name($stid, ":codmedico", $codMedico);
                oci_bind_by_name($stid, ":usucrea", $codMedico);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (\Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Antecedentes HC");
            }

            try {
                $indTipo = 'GE';
                foreach ($generales['habitos'] as $key => $value) {

                    $descOtros = null;

                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_FISIOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
            :secuenciaHC, :codMaestroDet, :indTipo, :descOtros ); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                    oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                    oci_bind_by_name($stid, ":codMaestroDet", $value);
                    oci_bind_by_name($stid, ":indTipo", $indTipo);
                    oci_bind_by_name($stid, ":descOtros", $descOtros);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                }
            } catch (\Throwable $th) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Antecedentes HC - Habitos");
            }

            // GRABAR FISIOLOGICOS


            try {

                $indTipo = 'FI';
                foreach ($fisiologicos['prenatales'] as $key => $value) {

                    $descOtros = null;
                    if ($value == "214") {
                        $descOtros = $fisiologicos['otrosPrenatales'];
                    }

                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_FISIOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
                :secuenciaHC, :codMaestroDet, :indTipo, :descOtros ); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                    oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                    oci_bind_by_name($stid, ":codMaestroDet", $value);
                    oci_bind_by_name($stid, ":indTipo", $indTipo);
                    oci_bind_by_name($stid, ":descOtros", $descOtros);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                }

                foreach ($fisiologicos['inmunizaciones'] as $key => $value) {

                    $descOtros = null;
                    if ($value == "341") {
                        $descOtros = $fisiologicos['otrosInmunizaciones'];
                    }

                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_FISIOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
                :secuenciaHC, :codMaestroDet, :indTipo, :descOtros ); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                    oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                    oci_bind_by_name($stid, ":codMaestroDet", $value);
                    oci_bind_by_name($stid, ":indTipo", $indTipo);
                    oci_bind_by_name($stid, ":descOtros", $descOtros);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                }

                if ($fisiologicos['parto']) {
                    $descOtros = null;
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_FISIOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
            :secuenciaHC, :codMaestroDet, :indTipo, :descOtros ); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                    oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                    oci_bind_by_name($stid, ":codMaestroDet", $fisiologicos['parto']);
                    oci_bind_by_name($stid, ":indTipo", $indTipo);
                    oci_bind_by_name($stid, ":descOtros", $descOtros);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                }
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Fisiologico");
            }

            //Grabar Ginecologico
            try {
                $edadMenarquia =  $ginecologicos['edadMenarquia'];
                $rcMenstruacion =  $ginecologicos['rcMenstruacion'];
                $cicloMenstruacion =  $ginecologicos['cicloMenstruacion'];
                $fechaFur =  $ginecologicos['fechaFur'];
                $fechaFpp =  $ginecologicos['fechaFpp'];
                $rs =  $ginecologicos['rs'];
                $disminorrea =  $ginecologicos['disminorrea'];
                $nroGestaciones =  $ginecologicos['nroGestaciones'];
                $paridad =  $ginecologicos['paridad'];
                $fechaFup =  $ginecologicos['fechaFup'];
                $nroCesareas =  $ginecologicos['nroCesareas'];
                $pap =  $ginecologicos['pap'];
                $mamografia =  $ginecologicos['mamografia'];
                $mac =  $ginecologicos['mac'];
                $otros =  $ginecologicos['otros'];
                $indReglaRegular =  $ginecologicos['indReglaRegular'];
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_GINECOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
            :secuenciaHC, :edadMenarquia, :rcMenstruacion, :cicloMenstruacion, :fechaFur, :fechaFpp, :rs, :disminorrea, :nroGestaciones, :paridad,
        :fechaFup, :nroCesareas, :pap, :mamografia, :mac, :otros, :indReglaRegular); END;");
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                oci_bind_by_name($stid, ":edadMenarquia", $edadMenarquia);
                oci_bind_by_name($stid, ":rcMenstruacion", $rcMenstruacion);
                oci_bind_by_name($stid, ":cicloMenstruacion", $cicloMenstruacion);
                oci_bind_by_name($stid, ":fechaFur", $fechaFur);
                oci_bind_by_name($stid, ":fechaFpp", $fechaFpp);
                oci_bind_by_name($stid, ":rs", $rs);
                oci_bind_by_name($stid, ":disminorrea", $disminorrea);
                oci_bind_by_name($stid, ":nroGestaciones", $nroGestaciones);
                oci_bind_by_name($stid, ":paridad", $paridad);
                oci_bind_by_name($stid, ":fechaFup", $fechaFup);
                oci_bind_by_name($stid, ":nroCesareas", $nroCesareas);
                oci_bind_by_name($stid, ":pap", $pap);
                oci_bind_by_name($stid, ":mamografia", $mamografia);
                oci_bind_by_name($stid, ":mac", $mac);
                oci_bind_by_name($stid, ":otros", $otros);
                oci_bind_by_name($stid, ":indReglaRegular", $indReglaRegular);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Ginecologicos");
            }



            // GRABAR PATOLOGICO
            try {
                $tipo = 'PA';
                $parent = '';
                foreach ($patologicos as $key => $value) {
                    $stid = oci_parse($conn, "BEGIN CENTRO_MEDICO.P_GRABAR_ANTE_HC_PATOLOGICO(:p1,:p2,:p3,:p4,:p5,:p6,:p7); END;");
                    oci_bind_by_name($stid, ":p1", $codGrupoCia);
                    oci_bind_by_name($stid, ":p2", $codLocal);
                    oci_bind_by_name($stid, ":p3", $codPaciente);
                    oci_bind_by_name($stid, ":p4", $secuenciaHC);
                    oci_bind_by_name($stid, ":p5", $value['cod_diagnostico']);
                    oci_bind_by_name($stid, ":p6", $tipo);
                    oci_bind_by_name($stid, ":p7", $parent);
                    oci_execute($stid);
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure("Error en Grabar Antecedentes");
            }
            try {
                $tipo = 'FA';
                foreach ($patologicosF as $key => $value) {
                    $stid = oci_parse($conn, "BEGIN CENTRO_MEDICO.P_GRABAR_ANTE_HC_PATOLOGICO(:p1,:p2,:p3,:p4,:p5,:p6,:p7); END;");
                    oci_bind_by_name($stid, ":p1", $codGrupoCia);
                    oci_bind_by_name($stid, ":p2", $codLocal);
                    oci_bind_by_name($stid, ":p3", $codPaciente);
                    oci_bind_by_name($stid, ":p4", $secuenciaHC);
                    oci_bind_by_name($stid, ":p5", $value['cod_diagnostico']);
                    oci_bind_by_name($stid, ":p6", $tipo);
                    oci_bind_by_name($stid, ":p7", $value['parentesco']);
                    oci_execute($stid);
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure("Error en Grabar Antecedentes Familiares");
            }


            // GRABAR ANTECEDENTES OTROS
            try {
                $idTratamiento = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                $registroAntecedentes = [
                    'ID_DATOS_ANTECEDENTES' => $idTratamiento,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_MEDICO' => $codMedico,
                    'FECHA' => new DateTime('NOW'),
                    'DIABETES' => $antecedentes['diabetes'],
                    'FIEBRE_REUMATICA' => $antecedentes['fiebre_reumatica'],
                    'ENFERMEDAD_HEPATICAS' => $antecedentes['enfermedad_hepaticas'],
                    'HEMORRAGIAS' => $antecedentes['hemorragias'],
                    'TUBERCULOSIS' => $antecedentes['tuberculosis'],
                    'ENFERMEDAD_CARDIOVASCULAR' => $antecedentes['enfermedad_cardiovascular'],
                    'REACCION_ANORMAL_LOCAL' => $antecedentes['reaccion_anormal_local'],
                    'ALERGIA_PENECILINA' => $antecedentes['alergia_penecilina'],
                    'ANEMIA' => $antecedentes['anemia'],
                    'ENFERMEDAD_RENAL' => $antecedentes['enfermedad_renal'],
                    'REACCION_ANORMAL_DROGAS' => $antecedentes['reaccion_anormal_drogas'],
                    'OTRAS' => $antecedentes['otras']
                ];
                Antecedentes::insert($registroAntecedentes);
                return CustomResponse::success();
            } catch (\Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Grabar Antecedentes");
            }
        }
    }

    /**
     * Guardar antecedentes fisiológicos (esta funcion graba un registro en una consulta - usar con cuidado)
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/setFisiologico",
     *     tags={"Antecedentes"},
     *     operationId="setFisiologico",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codLocal",
     *                  "secuenciaHC"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="secuenciaHC",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codMaestroDet",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="indTipo",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="descOtros",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codLocal": "001",
     *                  "codPaciente": "0010185756",
     *                  "codSecuencia": "1",
     *                  "codMaestroDet": "1",
     *                  "indTipo": "1",
     *                  "descOtros": "1"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function guardarFisiologicos(Request $request)
    {
        $codGrupoCia =  $request->input('codGrupoCia');
        $codLocal =  $request->input('codLocal');
        $codPaciente =  $request->input('codPaciente');
        $secuenciaHC =  $request->input('secuenciaHC');
        $codMaestroDet =  $request->input('codMaestroDet');
        $indTipo =  $request->input('indTipo');
        $descOtros =  $request->input('descOtros');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codPaciente' => 'required',
            'secuenciaHC' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            $conn = OracleDB::getConnection();
            try {
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_ANTE_HC_FISIOLOGICO1( :codgrupocia,  :codlocal, :codPaciente,
            :secuenciaHC, :codMaestroDet, :indTipo, :descOtros ); END;");
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                oci_bind_by_name($stid, ":secuenciaHC", $secuenciaHC);
                oci_bind_by_name($stid, ":codMaestroDet", $codMaestroDet);
                oci_bind_by_name($stid, ":indTipo", $indTipo);
                oci_bind_by_name($stid, ":descOtros", $descOtros);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Fisiologico");
            }
        }
    }

    /**
     * Obtener el historial de antecedentes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/antecedentes/getHistorial",
     *     tags={"Antecedentes"},
     *     operationId="getHistorial",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "fechaInicio",
     *                  "fechaFin"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="fechaInicio",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="fechaFin",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codPaciente": "0010185756",
     *                  "fechaInicio": "15-12-2021",
     *                  "fechaFin": "18-12-2021"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    function getHistorial(Request $request)
    {
        $codGrupoCia =  $request->input('codGrupoCia');
        $codPaciente =  $request->input('codPaciente');
        $fechaInicio =  $request->input('fechaInicio');
        $fechaFin =  $request->input('fechaFin');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'fechaInicio' => 'required',
            'fechaFin' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            $conn = OracleDB::getConnection();
            try {
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_LISTA_HIST_ANTECEDENTES( :codgrupocia,  :codPaciente, :fechaInicio, :fechaFin ); END;");
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codPaciente", $codPaciente);
                oci_bind_by_name($stid, ":fechaInicio", $fechaInicio);
                oci_bind_by_name($stid, ":fechaFin", $fechaFin);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
                $lista = [];
                while ($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    foreach ($row as $key => $value) {
                        $datos = explode('Ã', $value);
                        $abc = [
                            'key' => $datos[4],
                            'FECHA' => $datos[0],
                            'NUM_COLEGIO' => $datos[1],
                            'MEDICO' => $datos[2],
                        ];

                        $lista[] = $abc;
                    }
                }
                return CustomResponse::success("Datos encontrados", $lista);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Historial");
            }
        }
    }
}
