<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Alergias;
use App\Models\Interconsultas;
use App\Models\AtencionMedica;
use App\Models\Hospitalizaciones;
use App\Models\InterRecomendacion;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class PacientesController extends Controller
{
    /**
     * Obtener la lista de espera
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes",
     *     tags={"Pacientes"},
     *     operationId="pacientes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codEstado",
     *                  "codMedico"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codEstado",
     *                     type="string",
     *                     example="2",
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                     example="0000026144",
     *                 ),
     *                 @OA\Property(
     *                     property="consultorio",
     *                     type="string",
     *                     example="0",
     *                 ),
     *                 @OA\Property(
     *                     property="bus",
     *                     type="string",
     *                     example="0",
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
    public function listaEspera(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codEstado = $request->input('codEstado');
        $codMedico = $request->input('codMedico');
        $consultorio = $request->input('consultorio');
        $bus = $request->input('bus');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codEstado' => 'required',
            'codMedico' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result :=  CENTRO_MEDICO.F_LISTA_ESPERA_D(cCodGrupoCia_in => :codgrupocia, cTipoLista_in => :codestado, cCodMedico_in => :codmedico, cIdConsultorio_in => :idconsultorio, cIdBus_in => :idbus); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codestado", $codEstado);
                oci_bind_by_name($stid, ":codmedico", $codMedico);
                oci_bind_by_name($stid, ":idconsultorio", $consultorio);
                oci_bind_by_name($stid, ":idbus", $bus);
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
                                    'key' => $datos[2],
                                    'FECHA' => $datos[0],
                                    'HORA' => $datos[1],
                                    'COD_PACIENTE' => $datos[2],
                                    'PACIENTE' => $datos[3],
                                    'EDAD' => $datos[4],
                                    'ESTADO' => $datos[5],
                                    'NUM_ATEN_MED' => $datos[12],
                                ]
                            );
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);

                $filtroAlta = [];
                foreach ($lista as $key => $row) {
                    $atencionMedica =   AtencionMedica::query()
                        ->where([
                            'NUM_ATEN_MED' => $row['NUM_ATEN_MED'],
                            'COD_GRUPO_CIA' => $codGrupoCia,
                            'COD_MEDICO' => $codMedico,
                        ])->first();
                    $hospitalizacion = Hospitalizaciones::select('*')
                        ->where([
                            'COD_PACIENTE' => $row['COD_PACIENTE'],
                            'COD_GRUPO_CIA' => $codGrupoCia,
                        ])
                        ->first();
                    $lista[$key]['COD_CIA'] = $atencionMedica['cod_cia'];
                    if ($hospitalizacion) {
                        $lista[$key]['ASIGNADO'] = $hospitalizacion['asignado'];
                        $lista[$key]['MOTIVO_BAJA'] = $hospitalizacion['motivo_baja'];
                        if (!$hospitalizacion['motivo_baja']) {
                            array_push($filtroAlta, $lista[$key]);
                        }
                    } else {
                        array_push($filtroAlta, $lista[$key]);
                    }
                }

                return response()->json(
                    [
                        'success' => true,
                        'message' => 'Datos encontrados',
                        'data' => $filtroAlta,
                    ]
                );
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener bus medico
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getBusMedico",
     *     tags={"Pacientes"},
     *     operationId="getBusMedico",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "cmp"
     *               },
     *                 @OA\Property(
     *                     property="cmp",
     *                     type="string",
     *                     example="60245",
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
    public function getBusMedico(Request $request)
    {
        $cmp = $request->input('cmp');
        $validator = Validator::make($request->all(), [
            'cmp' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $data = DB::select("select * from cc_medico_x_bus b where b.num_cmp = ?", [$cmp]);
                return CustomResponse::success($data);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener tipo de acompañante
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getTipoAcomp",
     *     tags={"Pacientes"},
     *     operationId="getTipoAcomp",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
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
    public function obtenerTipoAcomp(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "begin :result := PTOVENTA_CME_ADM.CME_LISTA_TIPOS_PARENTESCO(cCodGrupoCia_in => :cCodGrupoCia_in); end;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
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
                                    'COD_MAES_DET' => $datos[0],
                                    'DESCRIPCION' => $datos[1],
                                ]
                            );
                        }
                    }
                }
                return CustomResponse::success("Datos Encontrados", $lista);
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener paciente
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getPaciente",
     *     tags={"Pacientes"},
     *     operationId="getPaciente",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente"
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function obtenerPaciente(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_OBTENER_DATOS_PACIENTE(ccodgrupocia_in=>:codgrupocia,ccodpaciente_in=>:codpaciente); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codpaciente", $codPaciente);
                oci_execute($stid);
                oci_execute($cursor);
                if ($stid) {
                    $row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS);
                    if ($row) {
                        return response()->json(
                            [
                                'success' => true,
                                'message' => 'Datos encontrados',
                                'data' => $row,
                            ]
                        );
                    } else {
                        throw new Exception('No hay datos');
                    }
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
     * Obtener alergias
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getAlergias",
     *     tags={"Pacientes"},
     *     operationId="getAlergias",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente"
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function obtenerAlergias(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = Alergias::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia])->get();
                return CustomResponse::success('Datos encontrados', $datos);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener estado hospitalización
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getHospitalizacion",
     *     tags={"Pacientes"},
     *     operationId="getHospitalizacion",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente"
     *               },
     *                 @OA\Property(
     *                     property="historiaClinica",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                     example="0010185756",
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
    public function obtenerEstadoHospi(Request $request)
    {
        $codGrupoCia = $request->input('historiaClinica');
        $codPaciente = $request->input('codPaciente');
        $validator = Validator::make($request->all(), [
            'historiaClinica' => 'required',
            'codPaciente' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = Hospitalizaciones::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'HISTORIA_CLINICA' => $codGrupoCia])->get();
                return CustomResponse::success('Datos encontrados', $datos);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Editar Alergias
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/updateAlergia",
     *     tags={"Pacientes"},
     *     operationId="updateAlergia",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "id",
     *                  "alergias",
     *                  "otros"
     *               },
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="alergias",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="otros",
     *                     type="string",
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
    public function editarAlergia(Request $request)
    {
        $ale = $request->input('alergias');
        $ot = $request->input('otros');
        $id = $request->input('id');
        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'alergias' => 'required',
            // 'otros'    => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                if ($ot) {
                    DB::update("update HCW_ALERGIAS set ALERGIAS = ?, OTROS = ? where ID_ALERGIAS = ?", [$ale, $ot, $id]);
                } else {
                    DB::update("update HCW_ALERGIAS set ALERGIAS = ? where ID_ALERGIAS = ?", [$ale, $id]);
                }
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Grabar Alergias
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/setAlergia",
     *     tags={"Pacientes"},
     *     operationId="setAlergia",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "alergias",
     *                  "otros",
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                     example="0000101857561",
     *                 ),
     *                 @OA\Property(
     *                     property="alergias",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="otros",
     *                     type="string",
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
    public function grabarAlergia(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $Alergia    = $request->input('alergias');
        $Otros       = $request->input('otros');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'alergias' => 'required',
            // 'otros' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();

                if ($Otros) {

                    $datosAlergia = [
                        'ID_ALERGIAS' => $id,
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_PACIENTE' => $codPaciente,
                        'ALERGIAS' => $Alergia,
                        'OTROS'   => $Otros
                    ];
                } else {
                    $datosAlergia = [
                        'ID_ALERGIAS' => $id,
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_PACIENTE' => $codPaciente,
                        'ALERGIAS' => $Alergia,
                    ];
                }

                Alergias::insert($datosAlergia);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Grabar Hospitalizacion
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/setHospitalizacion",
     *     tags={"Pacientes"},
     *     operationId="setHospitalizacion",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "hospitalizacion",
     *                  "urgencia",
     *                  "historiaClinica",
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                     example="0000101857561",
     *                 ),
     *                 @OA\Property(
     *                     property="hospitalizacion",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="urgencia",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="historiaClinica",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Grabados",     
     *     )
     * )
     */
    public function grabarHospi(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $HOSPITALIZACION    = $request->input('hospitalizacion');
        $URGENCIA       = $request->input('urgencia');
        $HISTORIA_CLINICA       = $request->input('historiaClinica');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                $datosAlergia = [
                    'ID_HOSPITALIZACION' => $id,
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'HOSPITALIZACION' => $HOSPITALIZACION,
                    'URGENCIA'   => $URGENCIA,
                    'HISTORIA_CLINICA'   => $HISTORIA_CLINICA,
                    'ASIGNADO'   => "0",
                    'NUM_ATENCION_MED' => $HISTORIA_CLINICA
                ];
                Hospitalizaciones::insert($datosAlergia);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Actualizar Hospitalizacion
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/updateHospitalizacion",
     *     tags={"Pacientes"},
     *     operationId="updateHospitalizacion",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "id",
     *                  "hospitalizacion",
     *                  "urgencia"
     *               },
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="hospitalizacion",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="urgencia",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Grabados",     
     *     )
     * )
     */
    public function actualizarHospi(Request $request)
    {
        $ale = $request->input('urgencia');
        $ot = $request->input('hospitalizacion');
        $id = $request->input('id');

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'hospitalizacion' => 'required',
            'urgencia'    => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                DB::update("update HCW_HOSPITALIZACION set URGENCIA = ?, HOSPITALIZACION = ? where ID_HOSPITALIZACION = ?", [$ale, $ot, $id]);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener interconsultas
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getInterconsultas",
     *     tags={"Pacientes"},
     *     operationId="getInterconsultas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
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
     *                     property="nroAtencion",
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
    public function obtenerInterconsultas(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');

        $nroAtencion = $request->input('nroAtencion');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'nroAtencion' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = Interconsultas::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $nroAtencion])->get();
                $recomendacion = InterRecomendacion::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $nroAtencion])->first();
                return CustomResponse::success('Datos encontrados', [$datos, $recomendacion]);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * @OA\Schema(
     *    type="object",
     *    schema="Interconsultas",
     *    title="Interconsultas",
     *    properties={
     *    @OA\Property(
     *      property="COD_PROD",
     *      type="string",
     *      example="001"
     *    ),
     *    @OA\Property(
     *      property="DESC_PROD",
     *      type="string",
     *      example="001"
     *    ),
     *    @OA\Property(
     *      property="NOM_LAB",
     *      type="string",
     *      example="001"
     *    ),
     *    @OA\Property(
     *      property="RUC",
     *      type="string",
     *      example="001"
     *    ),
     *    }
     *  ),
     */
    /**
     * Grabar interconsulta
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/setInterconsultas",
     *     tags={"Pacientes"},
     *     operationId="setInterconsultas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "interconsultas"
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
     *                     property="interconsultas",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Interconsultas")
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
    public function grabarInterconsultas(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $interconsultas = $request->input('interconsultas');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'interconsultas' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                Interconsultas::where(['COD_GRUPO_CIA' => $codGrupoCia, 'COD_PACIENTE' => $codPaciente])->delete();
                foreach ($interconsultas as $key => $value) {
                    $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                    $datos = [
                        'ID_INTERCONSULTAS' => $id,
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_PACIENTE' => $codPaciente,
                        'COD_PROD' => $value['COD_PROD'],
                        'DESC_PROD' => $value['DESC_PROD'],
                        'NOM_LAB' => $value['NOM_LAB'],
                        'RUC' => $value['RUC']
                    ];
                    Interconsultas::insert($datos);
                }
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
    /**
     * Registrar o actualiza paciente
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/upsertPaciente",
     *     tags={"Pacientes"},
     *     operationId="upsertPaciente",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  'COD_PACIENTE'
     *                  'DEP_UBIGEO'
     *                  'PRV_UBIGEO'
     *                  'DIS_UBIGEO'
     *                  'DIR_CLI'
     *                  'APE_PATERNO'
     *                  'APE_MATERNO'
     *                  'NOMBRE'
     *                  'ESTADO_CIVIL'
     *                  'COD_TIP_DOCUMENTO'
     *                  'NUM_DOCUMENTO'
     *                  'FEC_NAC_CLI'
     *                  'SEXO_CLI'
     *                  'COD_TIP_ACOM'
     *                  'COD_TIP_DOC_ACOM'
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Registrados",
     *     )
     * )
     */
    public function upsertPaciente(Request $request) {
        //$edad = $request->input('EDAD_CLI');
        $messageResponse = "";
        
        $ccodgrupocia_in = "001";
        $ccodlocal_in = "001";
        $vtipoddm = "";
        $vusuario = $request->input('USU_CREA_PACIENTE');
        $vnumhistoriaclinica = null;
        $vcodpaciente = $request->input('COD_PACIENTE');
        $vapepatpac_in = $request->input('APE_PATERNO');
        $vapematpac_in = $request->input('APE_MATERNO');
        $vnombrepac_in = $request->input('NOMBRE');
        $vtipdocpac_in = $request->input('COD_TIP_DOCUMENTO');
        $vnumdocpac_in = $request->input('NUM_DOCUMENTO');
        $vnumhcfisica_in = null;
        $vfechcfisica_in = null;
        $vtipacomp_in = $request->input('COD_TIP_ACOM');
        $vnombreacom_in = $request->input('NOM_ACOM');
        $vtipdocacom_in = $request->input('COD_TIP_DOC_ACOM');
        $vnumdocacom_in = $request->input('NUM_DOC_ACOM');
        $vsexo_in = $request->input('SEXO_CLI');
        $vestadocivil_in = $request->input('ESTADO_CIVIL');
        $vfecnac = $request->input('FEC_NAC_CLI');
        $vdireccion_in = $request->input('DIR_CLI');
        $vdepubigeo_in = $request->input('DEP_UBIGEO');
        $vpvrubigeo_in = $request->input('PRV_UBIGEO');
        $vdisubigeo_in = $request->input('DIS_UBIGEO');
        $vdeplugnac_in = null;
        $vpvrlugnac_in = null;
        $vdislugnac_in = null;
        $vdeplugpro_in = null;
        $vpvrlugpro_in = null;
        $vdislugpro_in = null;
        $vgradoins_in = null;
        $vocupacion_in = null;
        $vcentroel_in = null;
        $vgruposan_in = null;
        $vfactorrh_in = null;
        $vcorreo_in = $request->input('EMAIL');
        $vtelfijo_in = $request->input('FONO_CLI');
        $vtelcel_in = $request->input('CELL_CLI');

        if ($request->input('COD_PACIENTE') == '0') {
            $vtipoddm = "I";
        } else {
            $vtipoddm = "U";
        }
        
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'DEP_UBIGEO' => 'required',
            'PRV_UBIGEO' => 'required',
            'DIS_UBIGEO' => 'required',
            'DIR_CLI' => 'required',
            'APE_PATERNO' => 'required',
            'APE_MATERNO' => 'required',
            'NOMBRE' => 'required',
            'ESTADO_CIVIL' => 'required',
            'COD_TIP_DOCUMENTO' => 'required',
            'NUM_DOCUMENTO' => 'required',
            'FEC_NAC_CLI' => 'required',
            'SEXO_CLI' => 'required',
            'COD_TIP_ACOM' => 'required',
            'COD_TIP_DOC_ACOM' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $result = " ";
                $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.CME_GRABAR_PACIENTE_HC(
                    ccodgrupocia_in => :ccodgrupocia_in,
                    ccodlocal_in => :ccodlocal_in,
                    vusuario => :vusuario,
                    vtipoddm => :vtipoddm,
                    vnumhistoriaclinica => :vnumhistoriaclinica,
                    vcodpaciente => :vcodpaciente,
                    vapepatpac_in => :vapepatpac_in,
                    vapematpac_in => :vapematpac_in,
                    vnombrepac_in => :vnombrepac_in,
                    vtipdocpac_in => :vtipdocpac_in,
                    vnumdocpac_in => :vnumdocpac_in,
                    vnumhcfisica_in => :vnumhcfisica_in,
                    vfechcfisica_in => :vfechcfisica_in,
                    vtipacomp_in => :vtipacomp_in,
                    vnombreacom_in => :vnombreacom_in,
                    vtipdocacom_in => :vtipdocacom_in,
                    vnumdocacom_in => :vnumdocacom_in,
                    vsexo_in => :vsexo_in,
                    vestadocivil_in => :vestadocivil_in,
                    vfecnac => :vfecnac,
                    vdireccion_in => :vdireccion_in,
                    vdepubigeo_in => :vdepubigeo_in,
                    vpvrubigeo_in => :vpvrubigeo_in,
                    vdisubigeo_in => :vdisubigeo_in,
                    vdeplugnac_in => :vdeplugnac_in,
                    vpvrlugnac_in => :vpvrlugnac_in,
                    vdislugnac_in => :vdislugnac_in,
                    vdeplugpro_in => :vdeplugpro_in,
                    vpvrlugpro_in => :vpvrlugpro_in,
                    vdislugpro_in => :vdislugpro_in,
                    vgradoins_in => :vgradoins_in,
                    vocupacion_in => :vocupacion_in,
                    vcentroel_in => :vcentroel_in,
                    vgruposan_in => :vgruposan_in,
                    vfactorrh_in => :vfactorrh_in,
                    vcorreo_in => :vcorreo_in,
                    vtelfijo_in => :vtelfijo_in,
                    vtelcel_in => :vtelcel_in); END;");
                
                oci_bind_by_name($stid, ":result", $result, 20);
                oci_bind_by_name($stid, ":ccodgrupocia_in", $ccodgrupocia_in);
                oci_bind_by_name($stid, ":ccodlocal_in", $ccodlocal_in);
                oci_bind_by_name($stid, ":vusuario", $vusuario);
                oci_bind_by_name($stid, ":vtipoddm", $vtipoddm);
                oci_bind_by_name($stid, ":vnumhistoriaclinica", $vnumhistoriaclinica);
                oci_bind_by_name($stid, ":vcodpaciente", $vcodpaciente);
                oci_bind_by_name($stid, ":vapepatpac_in", $vapepatpac_in);
                oci_bind_by_name($stid, ":vapematpac_in", $vapematpac_in);
                oci_bind_by_name($stid, ":vnombrepac_in", $vnombrepac_in);
                oci_bind_by_name($stid, ":vtipdocpac_in", $vtipdocpac_in);
                oci_bind_by_name($stid, ":vnumdocpac_in", $vnumdocpac_in);
                oci_bind_by_name($stid, ":vnumhcfisica_in", $vnumhcfisica_in);
                oci_bind_by_name($stid, ":vfechcfisica_in", $vfechcfisica_in);
                oci_bind_by_name($stid, ":vtipacomp_in", $vtipacomp_in);
                oci_bind_by_name($stid, ":vnombreacom_in", $vnombreacom_in);
                oci_bind_by_name($stid, ":vtipdocacom_in", $vtipdocacom_in);
                oci_bind_by_name($stid, ":vnumdocacom_in", $vnumdocacom_in);
                oci_bind_by_name($stid, ":vsexo_in", $vsexo_in);
                oci_bind_by_name($stid, ":vestadocivil_in", $vestadocivil_in);
                oci_bind_by_name($stid, ":vfecnac", $vfecnac);
                oci_bind_by_name($stid, ":vdireccion_in", $vdireccion_in);
                oci_bind_by_name($stid, ":vdepubigeo_in", $vdepubigeo_in);
                oci_bind_by_name($stid, ":vpvrubigeo_in", $vpvrubigeo_in);
                oci_bind_by_name($stid, ":vdisubigeo_in", $vdisubigeo_in);
                oci_bind_by_name($stid, ":vdeplugnac_in", $vdeplugnac_in);
                oci_bind_by_name($stid, ":vpvrlugnac_in", $vpvrlugnac_in);
                oci_bind_by_name($stid, ":vdislugnac_in", $vdislugnac_in);
                oci_bind_by_name($stid, ":vdeplugpro_in", $vdeplugpro_in);
                oci_bind_by_name($stid, ":vpvrlugpro_in", $vpvrlugpro_in);
                oci_bind_by_name($stid, ":vdislugpro_in", $vdislugpro_in);
                oci_bind_by_name($stid, ":vgradoins_in", $vgradoins_in);
                oci_bind_by_name($stid, ":vocupacion_in", $vocupacion_in);
                oci_bind_by_name($stid, ":vcentroel_in", $vcentroel_in);
                oci_bind_by_name($stid, ":vgruposan_in", $vgruposan_in);
                oci_bind_by_name($stid, ":vfactorrh_in", $vfactorrh_in);
                oci_bind_by_name($stid, ":vcorreo_in", $vcorreo_in);
                oci_bind_by_name($stid, ":vtelfijo_in", $vtelfijo_in);
                oci_bind_by_name($stid, ":vtelcel_in", $vtelcel_in);
                oci_execute($stid);
                oci_close($conn);

                if ($request->input('COD_PACIENTE') == '0') {
                    $messageResponse = 'El paciente '.$vnombrepac_in.' '.$vapepatpac_in.' '.$vapematpac_in.' se registro con número de Historia Clinica '.$result;
                } else {
                    $messageResponse = 'Paciente modificado correctamente';
                }

                return CustomResponse::success($messageResponse, $result);
            } catch (\Throwable $th) {
                if (str_contains($th, 'ya se encuentra registrado')) {
                    return CustomResponse::failure('El DNI ya se encuentra registrado.');
                } else if (str_contains($th, 'nacimiento debe ser menor')) {
                    return CustomResponse::failure('El año de nacimiento debe ser menor o igual a la fecha actual.');
                } else if (str_contains($th, 'formato de fecha de naci')) {
                    return CustomResponse::failure('El formato de fecha de nacimiento debe ser DD/MM/yyyy.');
                } else {
                    return CustomResponse::failure($th->getMessage());
                }
            }
        }
    }
    /**
     * Obtener busqueda de pacientes
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/searchPacientes",
     *     tags={"Pacientes"},
     *     operationId="searchPacientes",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "DOC_TIP_DOCUMENTO"
     *               },
     *                 @OA\Property(
     *                     property="DOC_TIP_DOCUMENTO",
     *                     type="string",
     *                     example="01",
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
    public function searchPacientes(Request $request) {
        $apePaterno = $request->input('APE_PATERNO');
        $apeMaterno = $request->input('APE_MATERNO');
        $nombres = $request->input('NOMBRE');
        $codTipoDocumento = $request->input('DOC_TIP_DOCUMENTO');
        $numeroDocumento = $request->input('NUM_DOCUMENTO');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $cTipBusqueda = 'P';
        $cTipoComPago = '';
        $cNumComPago = '';

        $validator = Validator::make($request->all(), [
            'DOC_TIP_DOCUMENTO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result :=  PTOVENTA_CME_ADM.CME_LISTA_PACIENTES(
                    ccodgrupocia_in => :ccodgrupocia_in,
                    ccodlocal_in => :ccodlocal_in,
                    ctipbusqueda_in => :ctipbusqueda_in,
                    ctipcompago_in => :ctipcompago_in,
                    cnumcompago_in => :cnumcompago_in,
                    ctipdoc_in => :ctipdoc_in,
                    cnumdoc_in => :cnumdoc_in,
                    cnombre_in => :cnombre_in,
                    capepat_in => :capepat_in,
                    capemat_in => :capemat_in); END;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":ccodgrupocia_in", $codGrupoCia);
                oci_bind_by_name($stid, ":ccodlocal_in", $cCodLocal);
                oci_bind_by_name($stid, ":ctipbusqueda_in", $cTipBusqueda);
                oci_bind_by_name($stid, ":ctipcompago_in", $cTipoComPago);
                oci_bind_by_name($stid, ":cnumcompago_in", $cNumComPago);
                oci_bind_by_name($stid, ":ctipdoc_in", $codTipoDocumento);
                oci_bind_by_name($stid, ":cnumdoc_in", $numeroDocumento);
                oci_bind_by_name($stid, ":cnombre_in", $nombres);
                oci_bind_by_name($stid, ":capepat_in", $apePaterno);
                oci_bind_by_name($stid, ":capemat_in", $apeMaterno);
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
                                    'NUM_DOCUMENTO' => $datos[3],
                                    'DOC_TIP_DOCUMENTO' => $datos[2],
                                    'NOMBRE' => $datos[6],
                                    'APE_PATERNO' => $datos[4],
                                    'ESTADO' => $datos[8],
                                    'APE_MATERNO' => $datos[5],
                                ]
                            );
                        }
                    }
                }
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
    
                if(count($lista) <= 0) {
                    return CustomResponse::success('Pacientes no encontrados', $lista);
                } else {
                    return CustomResponse::success(count($lista) . ' Pacientes encontrados', $lista);
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
    /**
     * Obtener tipo de documento
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getTipoDoc",
     *     tags={"Pacientes"},
     *     operationId="getTipoDoc",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
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
    public function obtenerTipoDoc(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "begin :result := PTOVENTA_CME_ADM.CME_LISTA_TIPOS_DOCUMENTO(cCodGrupoCia_in => :cCodGrupoCia_in); end;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
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
                                    'COD_DOCUMENTO' => $datos[0],
                                    'DESCRIPCION' => $datos[1],
                                ]
                            );
                        }
                    }
                }
                return CustomResponse::success("Datos Encontrados", $lista);
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
    /**
     * Obtener estados civil
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/pacientes/getEstadoCivil",
     *     tags={"Pacientes"},
     *     operationId="getEstadoCivil",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
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
    public function obtenerEstadoCivil(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "begin :result := PTOVENTA_CME_ADM.CME_LISTA_TIPOS_EST_CIVIL(cCodGrupoCia_in => :cCodGrupoCia_in); end;");
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
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
                                    'COD_EST_CIVIL' => $datos[0],
                                    'DESCRIPCION' => $datos[1],
                                ]
                            );
                        }
                    }
                }
                return CustomResponse::success("Datos Encontrados", $lista);
                oci_free_statement($stid);
                oci_free_statement($cursor);
                oci_close($conn);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    public function obtenerAtencionPaciente(Request $request) {
        $nroAtencion = $request->input('nroAtencion');
        $codPaciente = $request->input('codPaciente');

        $validator = Validator::make($request->all(), [
            'nroAtencion' => 'required',
            'codPaciente' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, "SELECT
                A.COD_CIA, 
                A.ESTADO AS COD_ESTADO,
                A.COD_GRUPO_CIA,
                A.COD_LOCAL,
                A.COD_MEDICO,
                A.COD_PACIENTE,
                C.DESCRIPCION,
                A.NUM_ATEN_MED,
                A.NUM_ORDEN_VTA,
                DECODE(A.ESTADO, 'T', 'PEND.TRIAJE', 'P', 'PEND.ATENCION', 'C', 'EN CONSULTA', 'A', 'ATENDIDO', 'G', 'GRABADO TEMPORAL','ERROR') AS ESTADO,
                CONCAT(TRIM(M.DES_APE_MEDICO), CONCAT(' ', TRIM(M.DES_NOM_MEDICO))) AS MEDICO,
                CONCAT(P.APE_PAT_CLI, CONCAT(' ', CONCAT(P.APE_MAT_CLI, CONCAT(' ', P.NOM_CLI)))) AS NOMBRE,
                A.IND_ANULADO,
                C.ID_CONSULTORIO,
                H.NRO_HC_FISICA,
                H.NRO_HC_ACTUAL,
                D.DESCRIPCION AS ESPECIALIDAD,
                TO_CHAR(A.FEC_CREA, 'DD/MM/YYYY') AS FEC_CREA,
                TO_CHAR(A.FEC_CREA, 'HH24:MI:SS') AS FEC_CREA_HORA
            FROM 
                CME_ATENCION_MEDICA A,
                CC_CONSULTORIO C,
                CME_PACIENTE P,
                CME_HISTORIA_CLINICA H,
                MAESTRO_DETALLE D,
                MAE_MEDICO M
            WHERE
                P.Cod_Paciente = :codPaciente AND
                A.num_aten_med = :nroAtencion AND
                A.ID_CONSULTORIO = C.ID_CONSULTORIO AND
                A.COD_MAES_DET = D.COD_MAES_DET AND
                A.COD_GRUPO_CIA = H.COD_GRUPO_CIA AND
                A.COD_PACIENTE = H.COD_PACIENTE AND
                A.COD_MEDICO = M.COD_MEDICO
            ");

            oci_bind_by_name($stid, ":codPaciente", $codPaciente);
            oci_bind_by_name($stid, ":nroAtencion", $nroAtencion);
            oci_execute($stid);
            
            $lista = [];
            while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                array_push($lista, $row);
            }

            if (count($lista) == 1) {
                return CustomResponse::success("Dato Encontrados", $lista[0]);
            } else {
                return CustomResponse::success("Dato no Encontrado", []);
            }
            oci_close($conn);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure($th->getMessage());
        }
    }
}
