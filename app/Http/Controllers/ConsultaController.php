<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\EspecialidadIgnorada;
use App\Models\Estemat;
use App\Models\Interconsultas;
use App\Models\InterRecomendacion;
use App\Models\Procedimientos;
use App\Models\RecomendacionTratamiento;
use App\Models\ReporteAuditoria;
use App\Models\Sugerencia;
use App\Models\SugerenciaDetalle;
use App\Models\SugImagen;
use App\Models\SugImangen;
use App\Models\SugInterconsulta;
use App\Models\SugLaboratorio;
use App\Models\SugProcedimiento;
use App\Models\SugTratamiento;
use App\Models\PreTriaje;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Throwable;

use App\Models\Tratamiento;
use App\Models\TratamientoOdonto;
use DateTime;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{

    /**
     * Obtener la evolución del tratamiento odontológico
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/getEvolucionTratamientoOdonto",
     *     tags={"Consulta"},
     *     operationId="getEvolucionTratamientoOdonto",
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
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string",
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codPaciente": "0010185756",
     *                  "nroAtencion": "0000384457"
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
    public function getTratamientosPacienteOdonto(Request $request)
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
                $fechaActual = date("Y-m-d");
                $hace30dias = date("Y-m-d", strtotime($fechaActual . "- 3 month"));
                $datos = TratamientoOdonto::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $nroAtencion])
                    ->where('FECHA', '>=', new DateTime($hace30dias))
                    ->orderBy('fecha', 'DESC')->get();
                if ($datos) {
                    return CustomResponse::success('Datos encontrados.', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con tratamientos',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Eliminar la evolución del tratamiento odontológico
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/deleteEvolucionTratamientoOdonto",
     *     tags={"Consulta"},
     *     operationId="deleteEvolucionTratamientoOdonto",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "id"
     *               },
     *                 @OA\Property(
     *                     property="id",
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
    public function eliminarEvolucionTratamientoOdonto(Request $request)
    {
        $id = $request->input('id');
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $trata  = TratamientoOdonto::where('ID_DATOS_TRATAMIENTO', '=', $id);
                $trata->delete();
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Guardar la evolución del tratamiento odontológico
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/setEvolucionTratamientoOdonto",
     *     tags={"Consulta"},
     *     operationId="setEvolucionTratamientoOdonto",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codMedico",
     *                  "plan",
     *                  "descripcion",
     *                  "nroAtencion"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="plan",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="especialidad",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nombreMedico",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="descripcion",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string",
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codPaciente": "0010185756",
     *                  "codMedico": "0000026144",
     *                  "plan": "",
     *                  "especialidad": "",
     *                  "nombreMedico": "",
     *                  "descripcion": "",
     *                  "nroAtencion": ""
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
    public function guardarEvolucionTratamientoOdonto(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $codMedico = $request->input('codMedico');
        $plan = $request->input('plan');
        $especialidad = $request->input('especialidad');
        $nombreMedico = $request->input('nombreMedico');
        $descripcion = $request->input('descripcion');

        $nroAtencion = $request->input('nroAtencion');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'codMedico' => 'required',
            'plan' => 'required',
            'descripcion' => 'required',
            'nroAtencion' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {

                //ELIMINAR  
                TratamientoOdonto::query()->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_MEDICO' => $codMedico,
                    'NRO_ATENCION' => $nroAtencion,
                ])->delete();

                $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                $registroTratamiento = [
                    'ID_DATOS_TRATAMIENTO' => $id,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_MEDICO' => $codMedico,
                    'FECHA' => new DateTime('NOW'),
                    'PLAN_TRATAMIENTO' => $plan,
                    'DESCRIPCION_TRATAMIENTO' => $descripcion,
                    'ESPECIALIDAD' => $especialidad,
                    'NOMBRE_MEDICO' => $nombreMedico,
                    'NRO_ATENCION' => $nroAtencion,
                ];
                TratamientoOdonto::insert($registroTratamiento);
                return CustomResponse::success('grabado correctamente', $id);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener los tratamientos de un paciente
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/getEvolucionTratamiento",
     *     tags={"Consulta"},
     *     operationId="getEvolucionTratamiento",
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
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string",
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codPaciente": "0010185756",
     *                  "nroAtencion": "0000384457"
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
    public function getTratamientosPaciente(Request $request)
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
                $fechaActual = date("Y-m-d");
                $hace30dias = date("Y-m-d", strtotime($fechaActual . "- 3 month"));
                $datos = Tratamiento::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $nroAtencion])
                    ->where('FECHA', '>=', new DateTime($hace30dias))
                    ->orderBy('fecha', 'DESC')->get();
                if ($datos) {
                    return CustomResponse::success('Datos encontrados.', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con tratamientos',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Eliminar la evolución de un tratamiento
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/deleteEvolucionTratamiento",
     *     tags={"Consulta"},
     *     operationId="deleteEvolucionTratamiento",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "id"
     *               },
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                 ),
     *                 example={
     *                  "id": ""
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
    public function eliminarEvolucionTratamiento(Request $request)
    {
        $id = $request->input('id');
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $trata  = Tratamiento::where('ID_DATOS_TRATAMIENTO', '=', $id);
                $trata->delete();
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Guardar la evolución de un tratamiento
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/setEvolucionTratamiento",
     *     tags={"Consulta"},
     *     operationId="setEvolucionTratamiento",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codPaciente",
     *                  "codMedico",
     *                  "plan",
     *                  "descripcion",
     *                  "nroAtencion"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="plan",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="especialidad",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nombreMedico",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="descripcion",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="nroAtencion",
     *                     type="string",
     *                 ),
     *                 example={
     *                  "codGrupoCia": "001",
     *                  "codPaciente": "0010185756",
     *                  "codMedico": "0000026144",
     *                  "plan": "",
     *                  "especialidad": "",
     *                  "nombreMedico": "",
     *                  "descripcion": "",
     *                  "nroAtencion": ""
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
    public function guardarEvolucionTratamiento(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $codMedico = $request->input('codMedico');
        $plan = $request->input('plan');
        $especialidad = $request->input('especialidad');
        $nombreMedico = $request->input('nombreMedico');
        $descripcion = $request->input('descripcion');

        $nroAtencion = $request->input('nroAtencion');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'codMedico' => 'required',
            'plan' => 'required',
            'descripcion' => 'required',
            'nroAtencion' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {

                //ELIMINAR  
                Tratamiento::query()->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_MEDICO' => $codMedico,
                    'NRO_ATENCION' => $nroAtencion,
                ])->delete();

                $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                $registroTratamiento = [
                    'ID_DATOS_TRATAMIENTO' => $id,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_MEDICO' => $codMedico,
                    'FECHA' => new DateTime('NOW'),
                    'PLAN_TRATAMIENTO' => $plan,
                    'DESCRIPCION_TRATAMIENTO' => $descripcion,
                    'ESPECIALIDAD' => $especialidad,
                    'NOMBRE_MEDICO' => $nombreMedico,
                    'NRO_ATENCION' => $nroAtencion,
                ];
                Tratamiento::insert($registroTratamiento);
                return CustomResponse::success('grabado correctamente', $id);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * @OA\Schema(
     *    type="object",
     *    schema="Diagnostico",
     *    title="Diagnostico",
     *    properties={
     *    @OA\Property(
     *      property="cie",
     *      type="string"
     *    ),
     *    @OA\Property(
     *      property="tipodiagnostico",
     *      type="string"
     *    ),
     *    @OA\Property(
     *      property="coddiagnostico",
     *      type="string"
     *    ),
     *    @OA\Property(
     *      property="diagnostico",
     *      type="string"
     *    ),
     *    @OA\Property(
     *      property="secuencia",
     *      type="string"
     *    ),
     *    }
     *  ),
     */

    /**
     * @OA\Schema(
     *   type="object",
     *   schema="CabeceraDetalleModel",
     *   title="Cabecera Detalle Model",
     *   properties={
     *   @OA\Property(
     *     property="codprod",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="cantidad",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="valfrac",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="unidvta",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="frecuencia",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="duracion",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="viaadministracion",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="dosis",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="rucempresa",
     *     type="string"
     *   ),
     *   @OA\Property(
     *     property="recomendacionAplicar",
     *     type="string"
     *   ),
     *   }
     * ),
     */

    /**
     * Guardar la consulta
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/consulta/setConsulta",
     *     tags={"Consulta"},
     *     operationId="setConsulta",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codCia",
     *                  "codMedico",
     *                  "codLocal",
     *                  "numAtencion"
     *               },
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string",
     *                     example="001"
     *                 ),
     *                 @OA\Property(
     *                     property="codCia",
     *                     type="string",
     *                     example="001"
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string",
     *                     example="0000026144"
     *                 ),
     *                 @OA\Property(
     *                     property="codLocal",
     *                     type="string",
     *                     example="001"
     *                 ),
     *                 @OA\Property(
     *                     property="numAtencion",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="enfermedadActual",
     *                     type="object",
     *                      @OA\Property(
     *                          property="motivoConsulta",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="tipoInformante",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="tiempoEnfermedad",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="curso",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="relatoCronologico",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="apetito",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="sed",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="sueno",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="orina",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="deposicion",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="estadoFisico",
     *                     type="object",
     *                      @OA\Property(
     *                          property="estadoGeneral",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="estadoConciencia",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="exaFisicoDirigido",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="imc",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="medCintura",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="triaje",
     *                     type="object",
     *                      @OA\Property(
     *                          property="pa_1",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="pa_2",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="fr",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="fc",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="temp",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="peso",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="talla",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="satoxigeno",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="diagnostico",
     *                     type="array",
     *                      @OA\Items(ref="#/components/schemas/Diagnostico"),
     *                 ),
     *                 @OA\Property(
     *                     property="cabeceraReceta",
     *                     type="object",
     *                      @OA\Property(
     *                          property="cantitems",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="fechavigencia",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="cabeceraDetalle",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CabeceraDetalleModel"),
     *                 ),
     *                 @OA\Property(
     *                     property="tratamiento",
     *                     type="object",
     *                      @OA\Property(
     *                          property="validezreceta",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="indicacionesgen",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="estadoConsulta",
     *                     type="object",
     *                      @OA\Property(
     *                          property="codestadonew",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="codPaciente",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="estomatologico",
     *                     type="object",
     *                      @OA\Property(
     *                          property="fecha",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="cara",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="cuello",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="piel",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ganglios",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="atm",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="labios",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="carrillos",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="fondo_surco",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="periodonto",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="zona_retromolar",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="saliva",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="glandulas_salivales",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="lengua",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="paladar_duro",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="paladar_blando",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="piso_boca",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="orofaringe",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="indice_higiene_oral",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="hendidura_gingival",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="vitalidad_palpar",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="odusion",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="guia_anterior",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="interferencias",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="contacto_prematuro",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="rebordes_alveolare",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="tuberosidades",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="consultasProcedimientos",
     *                     type="object",
     *                      @OA\Property(
     *                          property="recomendacion",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="dataProcedimiento",
     *                          type="string"
     *                      )
     *                 ),
     *                 @OA\Property(
     *                     property="imagenes",
     *                     type="object",
     *                      @OA\Property(
     *                          property="recomendacion",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="dataProcedimiento",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="laboratorio",
     *                     type="object",
     *                      @OA\Property(
     *                          property="recomendacion",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="dataLaboratorio",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="desarrolloProcedimiento",
     *                     type="object",
     *                      @OA\Property(
     *                          property="relatoMedico",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="conclusion",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="observaciones",
     *                          type="string"
     *                      ),
     *                 ),
     *                 @OA\Property(
     *                     property="interconsultas",
     *                     type="string"
     *                 ),
     *                 
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function guardarConsulta(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codCia = $request->input('codCia');
        $codMedico = $request->input('codMedico');
        $nomMedico = $request->input('nomMedico');
        $especialidad = $request->input('especialidad');
        $codLocal = $request->input('codLocal');
        $numAtencion = $request->input('numAtencion');
        $enfermedadActualData = $request->input('enfermedadActual');
        $examenFisico = $request->input('estadoFisico');
        $triajeData = $request->input('triaje');
        $diagnosticoData = $request->input('diagnostico');
        $cabeceraRecetaData = $request->input('cabeceraReceta');
        $cabeceraDetalleData = $request->input('cabeceraDetalle');
        $tratamientoData = $request->input('tratamiento');
        $estadoConsultaData = $request->input('estadoConsulta');
        $codPaciente = $request->input('codPaciente');
        $nomPaciente = $request->input('nomPaciente');
        $examenClinico = $request->input('estomatologico');
        $consultasProcedimientos = $request->input('consultasProcedimientos');
        $imagenes = $request->input('imagenes');
        $laboratorio = $request->input('laboratorio');
        $desarrolloProcedimiento = $request->input('desarrolloProcedimiento');
        $interconsultas = $request->input('interconsultas');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codCia' => 'required',
            'codMedico' => 'required',
            'codLocal' => 'required',
            'numAtencion' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            $completos = [
                'enfermedadActual' => false,
                'examenFisico' => ['triaje' => false, 'examen' => false],
                'tratamiento' => false,
                'procedimiento' => ['observaciones' => false, 'dataProcedimiento' => false],
                'interconsulta' => ['observaciones' => false, 'dataInterconsulta' => false],
                'imagenes' => ['observaciones' => false, 'dataImagenes' => false],
                'laboratorio' => ['observaciones' => false, 'dataLaboratorio' => false],
            ];
            $conn = OracleDB::getConnection();
            //BORRAR TEMPORALES
            try {
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_BORRAR_GRABADOS_TEMPORAL11( :codgrupocia, :codcia, :codlocal, :numatencion); END;");
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codcia", $codCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatencion", $numAtencion);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en borrar Temporales");
            }
            // GRABAR OBSERVACIONES PROCEDIMIENTOS, IMAGENES Y LABORATORIO
            try {

                if ($consultasProcedimientos['recomendacion'] != '') {
                    $completos['procedimiento']['observaciones'] = true;
                }
                if ($imagenes['recomendacion'] != '') {
                    $completos['imagenes']['observaciones'] = true;
                }
                if ($laboratorio['recomendacion'] != '') {
                    $completos['laboratorio']['observaciones'] = true;
                }

                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.F_UPDATE_OTROS_OBS1( :codgrupocia, :codlocal, :numatendmed, :cObsTratamiento_in, :cObsProcedimiento_in, :cObsImagenes_in,
                     :cObsLaboratorio_in, :cOtrosProcedimiento_in, :cOtrosTransferencia_in, :cOtrosInterconsultas_in); END;");
                $df = 'N';
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatendmed",  $numAtencion);
                oci_bind_by_name($stid, ":cObsTratamiento_in", $tratamientoData['indicacionesgen']);
                oci_bind_by_name($stid, ":cObsProcedimiento_in", $consultasProcedimientos['recomendacion']);
                oci_bind_by_name($stid, ":cObsImagenes_in", $imagenes['recomendacion']);
                oci_bind_by_name($stid, ":cObsLaboratorio_in", $laboratorio['recomendacion']);
                oci_bind_by_name($stid, ":cOtrosProcedimiento_in", $df);
                oci_bind_by_name($stid, ":cOtrosTransferencia_in", $df);
                oci_bind_by_name($stid, ":cOtrosInterconsultas_in", $df);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Procedimientos");
            }
            //GRABAR PROCEDIMIENTOS 
            try {

                if (count($consultasProcedimientos['dataProcedimiento']) > 0) {
                    $completos['procedimiento']['dataProcedimiento'] = true;
                }

                $index = 1;
                foreach ($consultasProcedimientos['dataProcedimiento'] as $key => $value) {
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.P_AGREGA_PROCEDIMIENTOS1( :codgrupocia, :codlocal, :numatendmed, :codprod, :descprod, :nomlab,
                         :numruc, :vpos); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numatendmed",  $numAtencion);
                    oci_bind_by_name($stid, ":codprod", $value['COD_PROD']);
                    oci_bind_by_name($stid, ":descprod", $value['DESC_PROD']);
                    oci_bind_by_name($stid, ":nomlab", $value['NOM_LAB']);
                    oci_bind_by_name($stid, ":numruc", $value['RUC']);
                    oci_bind_by_name($stid, ":vpos", $index);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                    $index = $index + 1;
                }
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Procedimientos");
            }



            // GRABAR INTERCONSULTA

            try {
                //ELIMINAR
                /* 
                Interconsultas::query()->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'NRO_ATENCION' => $numAtencion,
                ])->delete(); */

                foreach ($interconsultas['dataProcedimiento'] as $key => $value) {
                    $id = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                    $datos = [
                        'ID_INTERCONSULTAS' => $id,
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_PACIENTE' => $codPaciente,
                        'COD_PROD' => $value['COD_PROD'],
                        'DESC_PROD' => $value['DESC_PROD'],
                        'NOM_LAB' => $value['NOM_LAB'],
                        'RUC' => $value['RUC'],
                        'NRO_ATENCION' => $numAtencion,
                    ];
                    Interconsultas::insert($datos);
                }

                $recomendacion = InterRecomendacion::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia, 'NRO_ATENCION' => $numAtencion])->first();

                if ($recomendacion) {

                    DB::update("update HCW_INTER_RECOMENDACION set RECOMENDACION = ? where COD_GRUPO_CIA = ? and COD_PACIENTE = ? and NRO_ATENCION= ?",  [$interconsultas['recomendacion'], $codGrupoCia, $codPaciente, $numAtencion]);
                } else {

                    InterRecomendacion::insert([
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_PACIENTE' => $codPaciente,
                        'NRO_ATENCION' => $numAtencion,
                        'RECOMENDACION' => $interconsultas['recomendacion']
                    ]);
                }

                if ($interconsultas['recomendacion'] != '') {
                    $completos['interconsulta']['observaciones'] = true;
                }
                if (count($interconsultas['dataProcedimiento']) > 0) {
                    $completos['interconsulta']['dataInterconsulta'] = true;
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
            //GRABAR IMAGENES
            try {
                $index = 1;
                foreach ($imagenes['dataProcedimiento'] as $key => $value) {
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.P_AGREGA_IMAGENES1( :codgrupocia, :codlocal, :numatendmed, :codprod, :descprod, :nomlab,
                     :numruc, :vpos); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numatendmed",  $numAtencion);
                    oci_bind_by_name($stid, ":codprod", $value['COD_PROD']);
                    oci_bind_by_name($stid, ":descprod", $value['DESC_PROD']);
                    oci_bind_by_name($stid, ":nomlab", $value['NOM_LAB']);
                    oci_bind_by_name($stid, ":numruc", $value['RUC']);
                    oci_bind_by_name($stid, ":vpos", $index);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                    $index = $index + 1;
                }
                if (count($imagenes['dataProcedimiento']) > 0) {
                    $completos['imagenes']['dataImagenes'] = true;
                }
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en las Imagenes");
            }
            //GRABAR LABORATORIO
            try {
                $index = 1;
                foreach ($laboratorio['dataLaboratorio'] as $key => $value) {
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := HHC_ADICIONAL.P_AGREGA_LABORATORIO1( :codgrupocia, :codlocal, :numatendmed, :codprod, :descprod, :nomlab,
                     :numruc, :vpos); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numatendmed",  $numAtencion);
                    oci_bind_by_name($stid, ":codprod", $value['COD_PROD']);
                    oci_bind_by_name($stid, ":descprod", $value['DESC_PROD']);
                    oci_bind_by_name($stid, ":nomlab", $value['NOM_LAB']);
                    oci_bind_by_name($stid, ":numruc", $value['RUC']);
                    oci_bind_by_name($stid, ":vpos", $index);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                    $index = $index + 1;
                }
                if (count($laboratorio['dataLaboratorio']) > 0) {
                    $completos['laboratorio']['dataLaboratorio'] = true;
                }
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en los Laboratorios");
            }
            // GRABAR EXAMEN CLINICO
            try {
                //ELIMINAR  
                Estemat::query()->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_MEDICO' => $codMedico,
                    'NRO_ATENCION' => $numAtencion,
                ])->delete();


                if (
                    $examenClinico['cara'] !== null ||
                    $examenClinico['cuello'] !== null ||
                    $examenClinico['piel'] !== null ||
                    $examenClinico['ganglios'] !== null ||
                    $examenClinico['atm'] !== null ||
                    $examenClinico['labios'] !== null ||
                    $examenClinico['carrillos'] !== null ||
                    $examenClinico['fondo_surco'] !== null ||
                    $examenClinico['periodonto'] !== null ||
                    $examenClinico['zona_retromolar'] !== null ||
                    $examenClinico['saliva'] !== null ||
                    $examenClinico['glandulas_salivales'] !== null ||
                    $examenClinico['lengua'] !== null ||
                    $examenClinico['paladar_duro'] !== null ||
                    $examenClinico['paladar_blando'] !== null ||
                    $examenClinico['piso_boca'] !== null ||
                    $examenClinico['orofaringe'] !== null ||
                    $examenClinico['indice_higiene_oral'] !== null ||
                    $examenClinico['hendidura_gingival'] !== null ||
                    $examenClinico['vitalidad_palpar'] !== null ||
                    $examenClinico['odusion'] !== null ||
                    $examenClinico['guia_anterior'] !== null ||
                    $examenClinico['interferencias'] !== null ||
                    $examenClinico['contacto_prematuro'] !== null ||
                    $examenClinico['rebordes_alveolare'] !== null ||
                    $examenClinico['tuberosidades'] !== null
                ) {

                    $idExamenClinico = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                    $registroExamenClinico = [
                        'ID_DATOS_ESTOMATOLOGICO' => $idExamenClinico,
                        'COD_PACIENTE' => $codPaciente,
                        'COD_GRUPO_CIA' => $codGrupoCia,
                        'COD_MEDICO' => $codMedico,
                        'FECHA' => $examenClinico['fecha'],
                        'CARA' => $examenClinico['cara'],
                        'CUELLO' => $examenClinico['cuello'],
                        'PIEL' => $examenClinico['piel'],
                        'GANGLIOS' => $examenClinico['ganglios'],
                        'ATM' => $examenClinico['atm'],
                        'LABIOS' => $examenClinico['labios'],
                        'CARRILLOS' => $examenClinico['carrillos'],
                        'FONDO_SURCO' => $examenClinico['fondo_surco'],
                        'PERIODONTO' => $examenClinico['periodonto'],
                        'ZONA_RETROMOLAR' => $examenClinico['zona_retromolar'],
                        'SALIVA' => $examenClinico['saliva'],
                        'GLANDULAS_SALIVALES' => $examenClinico['glandulas_salivales'],
                        'LENGUA' => $examenClinico['lengua'],
                        'PALADAR_DURO' => $examenClinico['paladar_duro'],
                        'PALADAR_BLANDO' => $examenClinico['paladar_blando'],
                        'PISO_BOCA' => $examenClinico['piso_boca'],
                        'OROFARINGE' => $examenClinico['orofaringe'],
                        'INDICE_HIGIENE_ORAL' => $examenClinico['indice_higiene_oral'],
                        'HENDIDURA_GINGIVAL' => $examenClinico['hendidura_gingival'],
                        'VITALIDAD_PALPAR' => $examenClinico['vitalidad_palpar'],
                        'ODUSION' => $examenClinico['odusion'],
                        'GUIA_ANTERIOR' => $examenClinico['guia_anterior'],
                        'INTERFERENCIAS' => $examenClinico['interferencias'],
                        'CONTACTO_PREMATURO' => $examenClinico['contacto_prematuro'],
                        'REBORDES_ALVEOLARE' => $examenClinico['rebordes_alveolare'],
                        'TUBEROSIDADES' => $examenClinico['tuberosidades'],
                        'NRO_ATENCION' => $numAtencion,
                    ];
                    Estemat::insert($registroExamenClinico);
                }
            } catch (\Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en examen clinico");
            }
            // ENFERMEDAD ACTUAL
            try {

                if (
                    ($enfermedadActualData['motivoConsulta'] && $enfermedadActualData['motivoConsulta'] !== '') ||
                    ($enfermedadActualData['tipoInformante'] && $enfermedadActualData['tipoInformante'] !== '') ||
                    ($enfermedadActualData['tiempoEnfermedad'] &&   $enfermedadActualData['tiempoEnfermedad'] !== '') ||
                    ($enfermedadActualData['curso'] && $enfermedadActualData['curso'] !== '') ||
                    ($enfermedadActualData['relatoCronologico'] && $enfermedadActualData['relatoCronologico'] !== '') ||
                    ($enfermedadActualData["apetito"] && $enfermedadActualData["apetito"] !== '') ||
                    ($enfermedadActualData["sed"] && $enfermedadActualData["sed"] !== '') ||
                    ($enfermedadActualData["sueno"] && $enfermedadActualData["sueno"] !== '') ||
                    ($enfermedadActualData["orina"] && $enfermedadActualData["orina"] !== '') ||
                    ($enfermedadActualData["deposicion"] && $enfermedadActualData['depsicion'] !== '')
                ) {
                    $completos['enfermedadActual'] = true;
                }

                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_AT_MED_ENFER_ACTUAL1( :codgrupocia, :codlocal, :numatencion, :motivoconsulta,:tipoinformante,:tiempoenfermedad,:formainicio,:signos,
                    :sintomas,
                    :curso,
                    :relatocronologico,
                    :tipoapetito,
                    :tiposed,
                    :tiposueno,
                    :tipoorina,
                    :tipodeposicion,
                    :usucrea
                ); END;");
                $motivoconsulta = $enfermedadActualData["motivoConsulta"];
                $tipoinformante = $enfermedadActualData["tipoInformante"];
                $tiempoenfermedad = $enfermedadActualData["tiempoEnfermedad"];
                $formainicio = '';
                $signos = '';
                $sintomas =  '';
                $curso =  $enfermedadActualData["curso"];
                $relatocronologico = $enfermedadActualData["relatoCronologico"];
                $tipoapetito = $enfermedadActualData["apetito"];
                $tiposed = $enfermedadActualData["sed"];
                $tiposueno = $enfermedadActualData["sueno"];
                $tipoorina = $enfermedadActualData["orina"];
                $tipodeposicion = $enfermedadActualData["deposicion"];


                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatencion", $numAtencion);
                oci_bind_by_name($stid, ":tipoinformante", $tipoinformante);
                oci_bind_by_name($stid, ":tiempoenfermedad", $tiempoenfermedad);
                oci_bind_by_name($stid, ":formainicio", $formainicio);
                oci_bind_by_name($stid, ":signos", $signos);
                oci_bind_by_name($stid, ":sintomas", $sintomas);
                oci_bind_by_name($stid, ":curso", $curso);
                oci_bind_by_name($stid, ":relatocronologico", $relatocronologico);
                oci_bind_by_name($stid, ":tipoapetito", $tipoapetito);
                oci_bind_by_name($stid, ":tiposed", $tiposed);
                oci_bind_by_name($stid, ":tiposueno", $tiposueno);
                oci_bind_by_name($stid, ":tipoorina", $tipoorina);
                oci_bind_by_name($stid, ":tipodeposicion", $tipodeposicion);
                oci_bind_by_name($stid, ":usucrea", $codMedico);
                oci_bind_by_name($stid, ":motivoconsulta", $motivoconsulta);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
                // }
            } catch (Throwable $e) {
                oci_commit($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en enfermedad Actual");
            }
            // TIAJE
            try {
                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_AT_MED_TRIAJE1( :codgrupocia, :codlocal, :numatencion, :pa_1, :pa_2,
                :fr, :fc, :temp, :peso, :talla, :usucrea, :satoxigeno); END;");

                $pa_1 = ($triajeData['pa_1']);
                $pa_2 = ($triajeData['pa_2']);
                $fr = ($triajeData['fr']);
                $fc = ($triajeData['fc']);
                $temp = ($triajeData['temp']);
                $peso = ($triajeData['peso']);
                $talla = ($triajeData['talla']);
                $satoxigeno = ($triajeData['satoxigeno']);

                if (($pa_1 !== 0 && $pa_1 !== '' && $pa_1) || ($pa_2 !== 0 && $pa_2 !== '' && $pa_2) ||
                    ($fr !== 0 && $fr !== '' && $fr) || ($fc !== 0 && $fc !== '' && $fc) ||
                    ($temp !== 0 && $temp !== '' && $temp) || ($peso !== 0 && $peso !== '' && $peso) ||
                    ($talla !== 0 && $talla !== '' && $talla) || ($satoxigeno !== 0 && $satoxigeno !== '' && $satoxigeno)
                ) {
                    $completos['examenFisico']['triaje'] = true;
                }

                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatencion", $numAtencion);
                oci_bind_by_name($stid, ":pa_1", $pa_1);
                oci_bind_by_name($stid, ":pa_2", $pa_2);
                oci_bind_by_name($stid, ":fr", $fr);
                oci_bind_by_name($stid, ":fc", $fc);
                oci_bind_by_name($stid, ":temp", $temp);
                oci_bind_by_name($stid, ":peso", $peso);
                oci_bind_by_name($stid, ":talla", $talla);
                oci_bind_by_name($stid, ":usucrea", $codMedico);
                oci_bind_by_name($stid, ":satoxigeno", $satoxigeno);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure([
                    "Error en triaje", $e->getMessage(), $pa_1,
                    $pa_2,
                    $fr,
                    $fc,
                    $temp,
                    $peso,
                    $talla,
                    $satoxigeno
                ]);
            }
            //GRABAR DIAGNOSTICO            
            try {
                $index = 1;
                foreach ($diagnosticoData as $key => $value) {
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_AT_MED_DIAGNOSTICO1( :codgrupocia, :codlocal, :numatencion, :csecuencia,
                     :coddiagnostico, :tipodiagnostico, :usucrea); END;");
                    $tipoD = $value['tipodiagnostico'] == 'PRESUNTIVO' ? 'P' : 'D';
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numatencion", $numAtencion);

                    oci_bind_by_name($stid, ":csecuencia", $index);
                    oci_bind_by_name($stid, ":coddiagnostico", $value['coddiagnostico']);
                    oci_bind_by_name($stid, ":tipodiagnostico", $tipoD);
                    oci_bind_by_name($stid, ":usucrea", $codMedico);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                    $index = $index + 1;
                }
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en Diagnostico");
            }

            // GUARDAR EXAMEN FISICO

            try {

                if (
                    ($examenFisico['estadoGeneral']  && $examenFisico['estadoGeneral'] !== '') ||
                    ($examenFisico['estadoConciencia']  && $examenFisico['estadoConciencia'] !== '') ||
                    ($examenFisico['exaFisicoDirigido']  && $examenFisico['exaFisicoDirigido'] !== '') ||
                    ($examenFisico['imc'] && $examenFisico['imc'] !== '' && $examenFisico['imc'] != 0)  ||
                    ($examenFisico['medCintura'] && $examenFisico['medCintura'] !== '')
                ) {
                    $completos['examenFisico']['examen'] = true;
                }



                $stid = oci_parse($conn, "BEGIN CENTRO_MEDICO.P_GRABAR_AT_MED_EXAMEN_FISICO(:codgrupocia, :codlocal, :numatencion, :estadoGeneral, 
                                                :estadoConciencia, :exaFisicoDirigido, :usucrea, :imc, :medCintura); END;");

                $imc = ($examenFisico['imc']);
                $medidaCintura = ($examenFisico['medCintura']);
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatencion", $numAtencion);

                oci_bind_by_name($stid, ":estadoGeneral", $examenFisico['estadoGeneral']);
                oci_bind_by_name($stid, ":estadoConciencia", $examenFisico['estadoConciencia']);
                oci_bind_by_name($stid, ":exaFisicoDirigido", $examenFisico['exaFisicoDirigido']);
                oci_bind_by_name($stid, ":usucrea", $codMedico);
                oci_bind_by_name($stid, ":imc", $imc);
                oci_bind_by_name($stid, ":medCintura", $medidaCintura);

                oci_execute($stid);
                // }
            } catch (\Throwable $th) {
                return CustomResponse::failure(["Error en Examen Fisico", $th->getMessage(), $imc, $medidaCintura]);
            }


            if (count($cabeceraDetalleData) > 0) {

                $completos['tratamiento'] = true;

                //CABECERA RECETA
                try {
                    $stid = oci_parse($conn, "BEGIN :numreceta :=  CENTRO_MEDICO.P_GRABA_RECETA_CABECERA1( :codgrupocia, :codlocal,:cantitems, :fechavigencia, :usucrea, :codmedico); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numreceta", $numreceta, 50);
                    oci_bind_by_name($stid, ":cantitems", $cabeceraRecetaData['cantitems'], -1, OCI_B_INT);
                    oci_bind_by_name($stid, ":fechavigencia", $cabeceraRecetaData['fechavigencia']);
                    oci_bind_by_name($stid, ":usucrea", $codMedico);
                    oci_bind_by_name($stid, ":codmedico", $codMedico);
                    oci_execute($stid);
                } catch (Throwable $e) {
                    oci_rollback($conn);
                    oci_close($conn);
                    return CustomResponse::failure("Error en cabecera receta");
                }
                // DETALLE RECETA 
                $ind = 1;
                try {
                    foreach ($cabeceraDetalleData as $key => $value) {
                        $cursor = oci_new_cursor($conn);
                        $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABA_RECETA_DETALLE1( :codgrupocia, :codlocal, :numpedrec,
                         :secuencia, :codprod, :cantidad, :valfrac, :unidvta, :frecuencia, :duracion, :viaadministracion, :dosis, :usuario,
                          :rucempresa ); END;");
                        oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                        oci_bind_by_name($stid, ":codlocal", $codLocal);
                        oci_bind_by_name($stid, ":numpedrec", $numreceta);
                        oci_bind_by_name($stid, ":secuencia", $ind);
                        oci_bind_by_name($stid, ":codprod", $value['codprod']);
                        oci_bind_by_name($stid, ":cantidad", $value['cantidad']);
                        oci_bind_by_name($stid, ":valfrac", $value['valfrac']);
                        oci_bind_by_name($stid, ":unidvta", $value['unidvta']);
                        oci_bind_by_name($stid, ":frecuencia", $value['frecuencia']);
                        oci_bind_by_name($stid, ":duracion", $value['duracion']);
                        oci_bind_by_name($stid, ":viaadministracion", $value['viaadministracion']);
                        oci_bind_by_name($stid, ":dosis", $value['dosis']);
                        oci_bind_by_name($stid, ":usuario", $codMedico);
                        oci_bind_by_name($stid, ":rucempresa", $value['rucempresa']);
                        oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                        oci_execute($stid);
                        oci_execute($cursor);
                        $ind = $ind + 1;


                        // GUARDAR RECOMENDACION TRATAMIENTO                                              


                        $resultado = RecomendacionTratamiento::where('NRO_RECETA', $numreceta)
                            ->where('ATENCION_MEDICA', $numAtencion)
                            ->where('COD_PROD', $value['codprod'])
                            ->first();

                        if ($resultado) {
                            $resultado->RECOMENDACION = $value['recomendacionAplicar'];
                            $resultado->save();
                        } else {
                            $idRecomendacion = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                            RecomendacionTratamiento::insert([
                                'ID_REC_TRAT' => $idRecomendacion,
                                'NRO_RECETA' => $numreceta,
                                'ATENCION_MEDICA' => $numAtencion,
                                'COD_PROD' => $value['codprod'],
                                'RECOMENDACION' => $value['recomendacionAplicar']
                            ]);
                        }
                    }
                } catch (Throwable $e) {
                    oci_rollback($conn);
                    oci_close($conn);
                    return CustomResponse::failure("Error en detalle de receta");
                }

                // TRATAMIENTO
                try {
                    $cursor = oci_new_cursor($conn);
                    $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.P_GRABAR_AT_MED_TRATAMIENTO1( :codgrupocia, :codlocal, :numatencion,
                 :numpedrec, :validezreceta, :indicacionesgen, :usuario); END;");
                    oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                    oci_bind_by_name($stid, ":codlocal", $codLocal);
                    oci_bind_by_name($stid, ":numatencion", $numAtencion);
                    oci_bind_by_name($stid, ":numpedrec", $numreceta);
                    oci_bind_by_name($stid, ":validezreceta", $tratamientoData['validezreceta']);
                    oci_bind_by_name($stid, ":indicacionesgen", $tratamientoData['indicacionesgen']);
                    oci_bind_by_name($stid, ":usuario", $codMedico);
                    oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                    oci_execute($stid);
                    oci_execute($cursor);
                } catch (Throwable $e) {
                    oci_rollback($conn);
                    oci_close($conn);
                    return CustomResponse::failure("Error en Tratamiento");
                }
            }

            // GRABAR DETALLE PROCEDIMIENTO

            try {
                Procedimientos::query()->where([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_MEDICO' => $codMedico,
                    'NRO_ATENCION' => $numAtencion,
                ])->delete();

                Procedimientos::insert([
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_MEDICO' => $codMedico,
                    'RELATO_MEDICO' =>  $desarrolloProcedimiento['relatoMedico'],
                    'CONCLUSION' => $desarrolloProcedimiento['conclusion'],
                    'OBSERVACIONES' =>  $desarrolloProcedimiento['observaciones'],
                    'NRO_ATENCION' => $numAtencion,
                    'ID_DATOS_CONSULTA' => round(((microtime(true)) * 1000)) . 'DT' . uniqid()
                ]);
            } catch (\Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure("Error en detalle procedimientos");
            }

            //ESTADO CONSULTA               
            try {
                $puntajes = DB::table('HCW_PESO_ESPECIALIDAD')->get();
                $data = [
                    'enfermedadActual' => $completos['enfermedadActual'],
                    'examenFisico' => $completos['examenFisico']['triaje'] || $completos['examenFisico']['examen'],
                    'tratamiento' => $completos['tratamiento'],
                    'procedimiento' => $completos['procedimiento']['observaciones'] || $completos['procedimiento']['dataProcedimiento'],
                    'interconsulta' => $completos['interconsulta']['observaciones'] || $completos['interconsulta']['dataInterconsulta'],
                    'imagenes' => $completos['imagenes']['observaciones'] || $completos['imagenes']['dataImagenes'],
                    'laboratorio' => $completos['laboratorio']['observaciones'] || $completos['laboratorio']['dataLaboratorio'],
                ];

                $puntaje = 0;
                foreach ($puntajes as $rowPuntaje) {
                    if ($completos['enfermedadActual']) {
                        if ($rowPuntaje->descripcion == 'enfermedad actual') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['examenFisico']['triaje'] || $completos['examenFisico']['examen']) {
                        if ($rowPuntaje->descripcion == 'examen fisico') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['tratamiento']) {
                        if ($rowPuntaje->descripcion == 'tratamiento') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['procedimiento']['observaciones'] || $completos['procedimiento']['dataProcedimiento']) {
                        if ($rowPuntaje->descripcion == 'procedimiento') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['interconsulta']['observaciones'] || $completos['interconsulta']['dataInterconsulta']) {
                        if ($rowPuntaje->descripcion == 'interconsulta') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['imagenes']['observaciones'] || $completos['imagenes']['dataImagenes']) {
                        if ($rowPuntaje->descripcion == 'imagenes') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                    if ($completos['laboratorio']['observaciones'] || $completos['laboratorio']['dataLaboratorio']) {
                        if ($rowPuntaje->descripcion == 'laboratorio') {
                            $puntaje = $puntaje + $rowPuntaje->peso;
                        }
                    }
                }

                $dataJson = json_encode($data);

                $reporteAuditoria = ReporteAuditoria::where('COD_PACIENTE', $codPaciente)
                    ->where('COD_MEDICO', $codMedico)
                    ->where('HC', $numAtencion)
                    ->first();

                if ($reporteAuditoria) {
                    DB::update('UPDATE HCW_REP_AUDITORIA SET COMPLETOS = :completos , PUNTAJE = :puntaje , ESPECIALIDAD = :especialidad , ESTADO = :estado WHERE ID = :id', [
                        'completos' => $dataJson,
                        'puntaje' => $puntaje,
                        'especialidad' => $especialidad,
                        'estado' => $estadoConsultaData['codestadonew'],
                        'id' => $reporteAuditoria['ID']
                    ]);
                    DB::update('UPDATE HCW_REP_AUDITORIA_V1 SET
                        enfermedad_actual = :enfermedadActual,
                        examen_fisico = :examenFisico,
                        tratamiento = :tratamiento,
                        procedimiento = :procedimiento,
                        interconsulta = :interconsulta,
                        imagenes = :imagenes,
                        laboratorio = :laboratorio
                        WHERE rep_auditoria = :idReporteAuditoria',
                    [
                        'enfermedadActual' => $data['enfermedadActual'] ? '1' : '0',
                        'examenFisico' => $data['examenFisico'] ? '1' : '0',
                        'tratamiento' => $data['tratamiento'] ? '1' : '0',
                        'procedimiento' => $data['procedimiento'] ? '1' : '0',
                        'interconsulta' => $data['interconsulta'] ? '1' : '0',
                        'imagenes' => $data['imagenes'] ? '1' : '0',
                        'laboratorio' => $data['laboratorio'] ? '1' : '0',
                        'idReporteAuditoria' => $reporteAuditoria['ID']
                    ]);
                } else {
                    $id_rep = round(((microtime(true)) * 1000)) . 'DT' . uniqid();
                    ReporteAuditoria::insert([
                        'COD_PACIENTE' => $codPaciente,
                        'COD_MEDICO' => $codMedico,
                        'ESPECIALIDAD' => $especialidad,
                        'HC' => $numAtencion,
                        'COMPLETOS' => $dataJson,
                        'PUNTAJE' => $puntaje,
                        'FECHA' => date('Y-m-d H:i:s'),
                        'NOM_PACIENTE' => $nomPaciente,
                        'NOM_MEDICO' => $nomMedico,
                        'estado' => $estadoConsultaData['codestadonew'],
                        'ID' => $id_rep
                    ]);

                    DB::insert('insert into HCW_REP_AUDITORIA_V1
                    (
                        id_rep,
                        rep_auditoria,
                        enfermedad_actual,
                        examen_fisico,
                        tratamiento,
                        procedimiento,
                        interconsulta,
                        imagenes,
                        laboratorio
                    ) values (?,?,?,?,?,?,?,?,?)',
                    [
                        round(((microtime(true)) * 1000)) . 'DTV' . uniqid(),
                        $id_rep,
                        $data['enfermedadActual'] ? '1' : '0',
                        $data['examenFisico'] ? '1' : '0',
                        $data['tratamiento'] ? '1' : '0',
                        $data['procedimiento'] ? '1' : '0',
                        $data['interconsulta'] ? '1' : '0',
                        $data['imagenes'] ? '1' : '0',
                        $data['laboratorio'] ? '1' : '0'
                    ]);
                }

                $cursor = oci_new_cursor($conn);
                $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_UPDATE_SOLICITUD_ATENCION1( :codgrupocia, :codlocal, :numatencion,
             :codestadonew, :usuario); END;");
                oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":numatencion", $numAtencion);
                oci_bind_by_name($stid, ":codestadonew", $estadoConsultaData['codestadonew']);
                oci_bind_by_name($stid, ":usuario", $codMedico);
                oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
                oci_execute($stid);
                oci_execute($cursor);
                return CustomResponse::success();
            } catch (Throwable $e) {
                oci_rollback($conn);
                oci_close($conn);
                return CustomResponse::failure(["Error en Estado Consulta", $e->getMessage()]);
            }
        }
    }

    public function guardarSugerencias(Request $request)
    {

        $cod_medico = $request->input("cod_medico");
        $diagnosticos = $request->input('diagnosticos');
        $imagenes = $request->input('imagenes');
        $laboratorios = $request->input('laboratorios');
        $tratamientos = $request->input('tratamientos');
        $interconsultas = $request->input('interconsultas');
        $procedimientos = $request->input('procedimientos');


        $validator = Validator::make($request->all(), [
            'diagnosticos' => 'required',
            // 'imagenes' => 'required',
            // 'laboratorios' => 'required',
            // 'tratamientos' => 'required',
            // 'interconsultas' => 'required',
            // 'procedimientos' => 'required',
            'cod_medico' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure($validator->errors()->all());
        }

        try {

            foreach ($diagnosticos as $key => $diagnostico) {
                $img2 = $imagenes;
                $lab2 = $laboratorios;
                $trat2 = $tratamientos;
                $int2 = $interconsultas;
                $pro2 = $procedimientos;
                // VERIFICAMOS SI EL DIAGNOSTICO YA EXISTE
                $dataDiagnostico = Sugerencia::query()
                    ->where([
                        'codDiagnostico' => $diagnostico['cie'],
                        'cod_medico' => $cod_medico,
                        // 'tipoDiagnostico' => $diagnostico['tipodiagnostico']
                    ])->first();
                // SI EXISTE EL DIAGNOSTICO LO EDITAMOS
                if ($dataDiagnostico) {
                    $detalleSugerencia = SugerenciaDetalle::query()
                        ->where(['codDiagnostico' => $diagnostico['cie'], 'cod_medico' => $cod_medico])->get();

                    foreach ($detalleSugerencia as $key => $detalle) {

                        switch ($detalle->tiposugerencia) {
                            case 'imagen':

                                foreach ($img2 as $key => $imagen) {
                                    if ($detalle['key'] == $imagen['key']) {
                                        // eliminar item imagen en imagenes
                                        $img2 = array_values(array_filter($img2, function ($value) use ($imagen) {
                                            return $value['key'] != $imagen['key'];
                                        }));
                                    }
                                }
                                break;
                            case 'laboratorio':

                                foreach ($lab2 as $key => $laboratorio) {
                                    if ($detalle['key'] == $laboratorio['key']) {
                                        // eliminar item laboratorio en laboratorios
                                        $lab2 = array_values(array_filter($lab2, function ($value) use ($laboratorio) {
                                            return $value['key'] != $laboratorio['key'];
                                        }));
                                    }
                                }
                                break;
                            case 'tratamiento':

                                foreach ($trat2 as $key => $tratamiento) {
                                    if ($detalle['key'] == $tratamiento['key']) {
                                        // eliminar item tratamiento en tratamientos
                                        $trat2 = array_values(array_filter($trat2, function ($value) use ($tratamiento) {
                                            return $value['key'] != $tratamiento['key'];
                                        }));
                                    }
                                }
                                break;
                            case 'interconsulta':

                                foreach ($int2 as $key => $interconsulta) {
                                    if ($detalle['key'] == $interconsulta['key']) {
                                        // eliminar item interconsulta en interconsultas
                                        $int2 = array_values(array_filter($int2, function ($value) use ($interconsulta) {
                                            return $value['key'] != $interconsulta['key'];
                                        }));
                                    }
                                }
                                break;
                            case 'procedimiento':

                                foreach ($pro2 as $key => $procedimiento) {
                                    if ($detalle['key'] == $procedimiento['key']) {
                                        // eliminar item procedimiento en procedimientos
                                        $pro2 = array_values(array_filter($pro2, function ($value) use ($procedimiento) {
                                            return $value['key'] != $procedimiento['key'];
                                        }));
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    setDatosSugerencias($img2, $lab2, $trat2, $int2, $pro2, $diagnostico['cie'], $cod_medico);
                } else {
                    // SI NO EXISTE EL DIAGNOSTICO LO CREAMOS

                    // SETEAMOS EL DIAGNOSTICO                    
                    Sugerencia::insert([
                        'codDiagnostico' => $diagnostico['cie'],
                        'cod_medico' => $cod_medico,
                        'tipoDiagnostico' => $diagnostico['tipodiagnostico'],
                        'nomDiagnostico' => $diagnostico['diagnostico']
                    ]);

                    setDatosSugerencias($imagenes, $laboratorios, $tratamientos, $interconsultas, $procedimientos, $diagnostico['cie'], $cod_medico);
                }
            }
            return CustomResponse::success('Exito');
        } catch (\Throwable $th) {
            return CustomResponse::failure("Error al guardar sugerencias");
        }
    }

    public function getSugerencias(Request $request)
    {
        $diagnosticos = $request->input('diagnosticos');
        $cod_medico = $request->input('cod_medico');

        $validator = Validator::make($request->all(), [
            'diagnosticos' => 'required',
            'cod_medico' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure($validator->errors()->all());
        }
        $imagenes = [];
        $laboratorios = [];
        $tratamientos = [];
        $interconsultas = [];
        $procedimientos = [];

        try {
            foreach ($diagnosticos as $key => $diagnostico) {
                $detalleSugerencia = SugerenciaDetalle::query()
                    ->where(['codDiagnostico' => $diagnostico['cie'], 'cod_medico' => $cod_medico])->get();

                foreach ($detalleSugerencia as $key => $detalle) {
                    switch ($detalle->tiposugerencia) {
                        case 'imagen':
                            $data = SugImagen::query()->where(["key" => $detalle->key])->get();
                            foreach ($data as $key => $dat) {
                                $imagenes[] = $dat;
                            }
                            break;
                        case 'laboratorio':
                            $data = SugLaboratorio::query()->where(["key" => $detalle->key])->get();
                            foreach ($data as $key => $dat) {
                                $laboratorios[] = $dat;
                            }
                            break;
                        case 'tratamiento':
                            $data = SugTratamiento::query()->where(["key" => $detalle->key])->get();
                            foreach ($data as $key => $dat) {
                                $tratamientos[] = $dat;
                            }
                            break;
                        case 'interconsulta':
                            $data = SugInterconsulta::query()->where(["key" => $detalle->key])->get();
                            foreach ($data as $key => $dat) {
                                $interconsultas[] = $dat;
                            }
                            break;
                        case 'procedimiento':
                            $data = SugProcedimiento::query()->where(["key" => $detalle->key])->get();
                            foreach ($data as $key => $dat) {
                                $procedimientos[] = $dat;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }

            // quitar los items duplicados de los array de sugerencias por key

            $imagenes = unique_multidim_array($imagenes, 'key');
            $laboratorios = unique_multidim_array($laboratorios, 'key');
            $tratamientos = unique_multidim_array($tratamientos, 'key');
            $interconsultas = unique_multidim_array($interconsultas, 'key');
            $procedimientos = unique_multidim_array($procedimientos, 'key');

            return CustomResponse::success('Datos Encontrados', ['imagenes' => $imagenes, 'laboratorios' => $laboratorios, 'tratamientos' => $tratamientos, 'interconsultas' => $interconsultas, 'procedimientos' => $procedimientos]);
        } catch (\Throwable $th) {
            return CustomResponse::failure("Error al obtener sugerencias");
        }
    }

    function listaHistoriaMedica(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codPaciente = $request->input('codPaciente');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codPaciente' => 'required',
            'fechaInicio' => 'required',
            'fechaFin' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_LISTA_HIST_ATEN_MEDICA( :codgrupocia, :codpaciente, :fechainicio, :fechafin ); END;");
            oci_bind_by_name($stid, ":codgrupocia",  $codGrupoCia);
            oci_bind_by_name($stid, ":codpaciente",  $codPaciente);
            oci_bind_by_name($stid, ":fechainicio",  $fechaInicio);
            oci_bind_by_name($stid, ":fechafin",  $fechaFin);
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);
            if ($stid) {
                $lista = [];
                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    foreach ($row as $key => $value) {
                        $datos = explode('Ã', $value);
                        $abc = [
                            'key' => $datos[8],
                            'FECHA' => $datos[0],
                            'CENTRO_MEDICO' => $datos[1],
                            'NUM_COLEGIO' => $datos[2],
                            'MEDICO' => $datos[3],
                            'ESPECIALIDAD' => $datos[4],
                            'NRO_ATEN_MED' => $datos[8]
                        ];

                        $lista[] = $abc;
                    }
                }
                return CustomResponse::success('Datos Encontrados.', $lista);
            }
            // $abc = oci_fetch_all($cursor, $data, null, null, OCI_FETCHSTATEMENT_BY_ROW);
            // return CustomResponse::success('Datos Encontrados', $data);
        } catch (\Throwable $th) {
            return CustomResponse::failure("Error al obtener historia medica");
        }
    }

    function getListaIgnorados(Request $request)
    {
        try {

            $respuesta = EspecialidadIgnorada::get();
            return CustomResponse::success('Datos Encontrados.', $respuesta);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error: ' . $th->getMessage());
        }
    }

    function getExamenesLaboratorio(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $dni = $request->input('dni');
        $paciente = $request->input('paciente');
        $cmp = $request->input('cmp');
        $dniTodos = "";
        $pacienteTodos = "";
        $cmpTodos = "";

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'fechaInicio' => 'required',
            'fechaFin' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        try {

            if ($dni == "") {
                $dni = "";
                $dniTodos = "VT";
            }
            if ($paciente == "") {
                $paciente = "";
                $pacienteTodos = "VT";
            }
            if ($cmp == "") {
                $cmp = "";
                $cmpTodos = "VT";
            }

            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := CENTRO_MEDICO.F_LISTA_EXAMENES_PAC( :codGrupoCia, :fechaInicio, :fechaFin, :dni, :paciente, :cmp, :dniTodos, :pacienteTodos, :cmpTodos); END;");
            oci_bind_by_name($stid, ":codGrupoCia",  $codGrupoCia);
            oci_bind_by_name($stid, ":fechaInicio",  $fechaInicio);
            oci_bind_by_name($stid, ":fechaFin",  $fechaFin);
            oci_bind_by_name($stid, ":dni",  $dni);
            oci_bind_by_name($stid, ":paciente",  $paciente);
            oci_bind_by_name($stid, ":cmp",  $cmp);
            oci_bind_by_name($stid, ":dniTodos",  $dniTodos);
            oci_bind_by_name($stid, ":pacienteTodos",  $pacienteTodos);
            oci_bind_by_name($stid, ":cmpTodos",  $cmpTodos);
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);

            $lista = [];
            if ($stid) {
                $cont = 0;
                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    foreach ($row as $key => $value) {
                        $temp = [];
                        $datos = explode('Ã', $value);
                        $keys = explode("||'Ã'||", $key);

                        $contador = 0;
                        foreach ($keys as $key1 => $value1) {
                            $temp[$value1] = $datos[$contador];
                            $contador++;
                        }
                        $temp["key"] = $cont;
                        $lista[] = $temp;
                        $cont++;
                    }
                }
            }
            return CustomResponse::success('Tabla de Paneles encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error: ' . $th->getMessage());
        }
    }

    public function upsertTriaje(Request $request) {
        $codPaciente = $request->input('COD_PACIENTE');
        $nroHC = $request->input('NUM_HC');
        $asignacion = $request->input('ASIGNADO');
        $fechaNac = $request->input('FECHA_NAC');
        $paciente = $request->input('PACIENTE');
        $usuCrea = $request->input('USU_CREA');
        $numAtencionMedica = $request->input('NUM_ATEN_MED');
        // $actualizar = $request->input('ACTUALIZAR');

        $nPA1 = $request->input('PA1');
        $nPA2 = $request->input('PA2');
        $nFR = $request->input('FR');
        $nFC = $request->input('FC');
        $nTemp = $request->input('TEMP');
        $nPeso = $request->input('PESO');
        $nTalla = $request->input('TALLA');
        $nSaturacion = $request->input('SATURACION');

        $codGrupoCia = '001';
        $cCodCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'PA1' => 'required',
            'PA2' => 'required',
            'FR' => 'required',
            'FC' => 'required',
            'TEMP' => 'required',
            'PESO' => 'required',
            'TALLA' => 'required',
            'SATURACION' => 'required',
            'COD_PACIENTE' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            // $triajeSearch = DB::select('SELECT * FROM HCW_DATOS_CLI_TICKET c WHERE c.COD_PACIENTE = ?', [$codPaciente]);

            // if ($triajeSearch) {
            //     DB::update('UPDATE HCW_DATOS_TRIAJE_TICKET t SET t.PA_1=?,t.PA_2=?,t.FR=?,t.FC=?,t.TEMP=?,t.PESO=?,t.TALLA=?,t.SATURACION_OXIGENO=? WHERE t.ID=?', [
            //         $nPA1,
            //         $nPA2,
            //         $nFR,
            //         $nFC,
            //         $nTemp,
            //         $nPeso,
            //         $nTalla,
            //         $nSaturacion,
            //         $triajeSearch[0]->triaje
            //     ]);
            //     DB::update('UPDATE HCW_DATOS_CLI_TICKET t SET t.USU_MOD=?,t.FEC_MOD=?,t.NUM_ATEN_MED=? WHERE t.TRIAJE=?', [
            //         $usuCrea,
            //         date('Y-m-d H:i:s'),
            //         $numAtencionMedica,
            //         $triajeSearch[0]->triaje
            //     ]);
            //     return CustomResponse::success('Triaje actualizado.');
            // }

            $idTriaje = round(((microtime(true)) * 1000)) . 'DTT' . uniqid();
            $data = DB::insert('INSERT INTO HCW_DATOS_TRIAJE_TICKET (ID,PA_1,PA_2,FR,FC,TEMP,PESO,TALLA,SATURACION_OXIGENO) VALUES(?,?,?,?,?,?,?,?,?)', [
                $idTriaje,
                $nPA1,
                $nPA2,
                $nFR,
                $nFC,
                $nTemp,
                $nPeso,
                $nTalla,
                $nSaturacion,
            ]);
            $dataTriaje = DB::insert('INSERT INTO HCW_DATOS_CLI_TICKET (ID,COD_GRUPO_CIA,COD_CIA,COD_LOCAL,COD_PACIENTE,NRO_HC,ASIGNADO,PACIENTE,FECHA_NAC,USU_CREA,TRIAJE,NUM_ATEN_MED) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', [
                round(((microtime(true)) * 1000)) . 'DCT' . uniqid(),
                $codGrupoCia,
                $cCodCia,
                $cCodLocal,
                $codPaciente,
                $nroHC,
                $asignacion,
                $paciente,
                $fechaNac,
                $usuCrea,
                $idTriaje,
                $numAtencionMedica
            ]);
            return CustomResponse::success('Triaje Registrado.', $dataTriaje);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            if (str_contains($e, 'value larger than specified precision')) {
                return CustomResponse::failure('Datos invalidos.');
            }
            return CustomResponse::failure($e->getMessage());
        }
    }

    public function traerExistePacienteTriaje (Request $request) {
        $codPaciente = $request->input('COD_PACIENTE');

        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $data = DB::select('select count(*) as exist from HCW_CAMAS t where t.paciente = ?', [$codPaciente]);
            if ($data[0]->exist == 1) {
                return CustomResponse::failure('Este paciente ya esta asignado.');
            }
            return CustomResponse::success('Continuar con el triaje.');
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores');
        }
    }

    public function traerTriaje(Request $request) {
        $codPaciente = $request->input('COD_PACIENTE');

        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $data = DB::select('SELECT t.PA_1, t.PA_2, t.FC, t.FR, t.PESO, t.TEMP, t.TALLA, t.SATURACION_OXIGENO FROM HCW_DATOS_CLI_TICKET c, HCW_DATOS_TRIAJE_TICKET t WHERE c.TRIAJE = t.ID AND c.COD_PACIENTE = ?', [$codPaciente]);
            if (count($data) === 1) {
                return CustomResponse::success('Datos encontrados.', $data[0]);
            } else {
                return CustomResponse::success('No encontrado.', []);
            }
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores');
        }
    }

    public function getListaTriaje() {
        try {
            $data = DB::select("SELECT t.*, a.estado FROM 
                                HCW_DATOS_CLI_TICKET T,
                                CME_ATENCION_MEDICA A,
                                HCW_HOSPITALIZACION HOSP 
                                WHERE T.NUM_ATEN_MED = A.NUM_ATEN_MED 
                                AND EXISTS (
                                SELECT * FROM HCW_CAMAS CAM WHERE 
                                T.COD_PACIENTE = CAM.PACIENTE
                                AND HOSP.NUM_ATEN_MED = T.NUM_ATEN_MED
                                AND HOSP.ASIGNADO = 1
                                )
                                AND T.ASIGNADO IS NOT NULL ORDER BY T.FEC_CREA DESC");
            $lista = [];

            foreach ($data as $key) {
                array_push($lista, [
                    'PACIENTE' => $key->paciente,
                    'COD_GRUPO_CIA' => $key->cod_grupo_cia,
                    'COD_CIA' => $key->cod_cia,
                    'COD_LOCAL' => $key->cod_local,
                    'NRO_HC' => $key->nro_hc,
                    'ASIGNADO' => $key->asignado,
                    'FEC_NAC' => $key->fecha_nac,
                    'FEC_CREA' => $key->fec_crea,
                    'key' => $key->id,
                    'COD_PACIENTE' => $key->cod_paciente,
                    'NUM_ATEN_MED' => $key->num_aten_med,
                    'ESTADO' => $key->estado
                ]);
            }
            
            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Error en los servidores');
        }
    }

    // Pre Triaje

    public function generarPreTriaje(Request $request) {
        $codPaciente = $request->input('COD_PACIENTE');
        $nroHC = $request->input('NUM_HC');
        $fechaToma = $request->input('FECHA_TOMA');
        $paciente = $request->input('PACIENTE');
        $usuCrea = $request->input('USU_CREA');
        $codUsuCrea = $request->input('COD_USU_CREA');

        $nPA1 = $request->input('PA1');
        $nPA2 = $request->input('PA2');
        $nFR = $request->input('FR');
        $nFC = $request->input('FC');
        $nTemp = $request->input('TEMP');
        $nPeso = $request->input('PESO');
        $nTalla = $request->input('TALLA');
        $nSaturacion = $request->input('SATURACION');

        $codGrupoCia = '001';
        $cCodCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'PA1' => 'required',
            'PA2' => 'required',
            'FR' => 'required',
            'FC' => 'required',
            'TEMP' => 'required',
            'PESO' => 'required',
            'TALLA' => 'required',
            'SATURACION' => 'required',
            'COD_PACIENTE' => 'required',
            'COD_USU_CREA' =>  'required',
            'FECHA_TOMA' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $dataPreTriaje = DB::insert('INSERT INTO HCW_PRE_TRIAJE (
                ID,
                COD_GRUPO_CIA,
                COD_CIA,
                COD_LOCAL,
                COD_PACIENTE,
                NRO_HC,
                PACIENTE,
                FEC_TOMA,
                USU_CREA,
                COD_USU_CREA,
                PA_1,
                PA_2,
                FR,
                FC,
                TEMP,
                PESO,
                TALLA,
                SATURACION_OXIGENO
            ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                round(((microtime(true)) * 1000)) . 'PT' . uniqid(),
                $codGrupoCia,
                $cCodCia,
                $cCodLocal,
                $codPaciente,
                $nroHC,
                $paciente,
                $fechaToma,
                $usuCrea,
                $codUsuCrea,
                $nPA1,
                $nPA2,
                $nFR,
                $nFC,
                $nTemp,
                $nPeso,
                $nTalla,
                $nSaturacion
            ]);
            return CustomResponse::success('Pre triaje generado.', $dataPreTriaje);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th, 'value larger than specified precision')) {
                return CustomResponse::failure('Datos invalidos.');
            }
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function busquedaPreTriaje(Request $request) {
        $fechaInicio = $request->input('FECHA_INICIO');
        $fechaFin = $request->input('FECHA_FIN');
        $cmp = $request->input('NUM_CMP');
        $codPaciente = $request->input('COD_PACIENTE');

        $validator = Validator::make($request->all(), [
            'FECHA_INICIO' => 'required',
            'FECHA_FIN' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Rango de fecha necesario.');
        }

        try {
            $data = PreTriaje::select('*')
                ->whereBetween('FEC_TOMA', [$fechaInicio, $fechaFin])
                ->orderBy('FEC_TOMA', 'DESC')
                ->get();

            return CustomResponse::success('Datos obtenidos', $data);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Ocurrió un error en los servidores');
        }
    }

    public function busquedaPreTriajePacientes(Request $request) {
        $nomPaciente = $request->input('NOM_PACIENTE');

        $validator = Validator::make($request->all(), [
            'NOM_PACIENTE' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se esta ingresando el nombre del paciente.');
        }

        try {
            $data = PreTriaje::select('PACIENTE', 'COD_PACIENTE as KEY', 'PACIENTE as VALUE')
                ->where('PACIENTE', 'like', '%'. strtoupper($nomPaciente) .'%')
                ->limit(20)
                ->get();
            
            return CustomResponse::success('Pacientes encontrados.', $data);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function busquedaPreTriajeMedicos(Request $request) {
        $nomMedico = $request->input('NOM_MEDICO');

        $validator = Validator::make($request->all(), [
            'NOM_MEDICO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se esta ingresando el nombre del médico.');
        }

        try {
            $data = PreTriaje::select(DB::raw('concat(MAE_MEDICO.DES_NOM_MEDICO, concat(\' \', trim(MAE_MEDICO.DES_APE_MEDICO))) as MEDICO'), 'COD_USU_CREA as KEY', DB::raw('concat(MAE_MEDICO.DES_NOM_MEDICO, concat(\' \', trim(MAE_MEDICO.DES_APE_MEDICO))) as VALUE'))
                ->join('MAE_MEDICO', 'NUM_CMP', '=', 'COD_USU_CREA')
                ->where('MAE_MEDICO.DES_NOM_MEDICO', 'like', '%'. strtoupper($nomMedico) .'%')
                ->limit(20)
                ->get();
            
            return CustomResponse::success('Medicos encontrados.', $data);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    // CONSULTA ADMISION

    public function busquedaPedidoCabecera(Request $request) {
        $numPedido = $request->input('NUM_PEDIDO');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $numComprobante = '';
        $tipoPago = '';

        $validator = Validator::make($request->all(), [
            'NUM_PEDIDO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se ingreso Número de Pedido.');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.CAJ_LISTA_CABECERA_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                cNumCompPag => :cNumCompPag,
                cFlagTipProcPago => :cFlagTipProcPago
            );END;");

            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumPedVta_in", $numPedido);
            oci_bind_by_name($stid, ":cNumCompPag", $numComprobante);
            oci_bind_by_name($stid, ":cFlagTipProcPago", $tipoPago);
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
                                'FECHA' => $datos[1],
                                'MONTO' => $datos[2],
                                'NUM_DOCUMENTO' => $datos[3],
                                'PACIENTE' => $datos[4],
                                'CAJERO' => $datos[5],
                                'FEC_PENDIENTE' => $datos[6],
                                'FEC_ENVIO_CONSULTA' => $datos[7],
                                'FEC_INI_CONSULTA' => $datos[8],
                                'FEC_FIN_CONSULTA' => $datos[9],
                                '10' => $datos[10],
                                'COD_PACIENTE' => $datos[11],
                                '12' => $datos[12],
                                '13' => $datos[13],
                                'NUM_COMP_PAGO' => $datos[14],
                                '15' => $datos[15]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) == 0) {
                return CustomResponse::failure('No existen información con este número de pedido.');
            }
            
            return CustomResponse::success('Cabecera pedido encontrado.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th, 'No puede ingresar el pedido por esta opción')) {
                return CustomResponse::failure('No puede ingresar el pedido por esta opción.');
            }
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function busquedaPedidoDetalles(Request $request) {
        $numPedido = $request->input('NUM_PEDIDO');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $numComprobante = '';
        $tipoPago = '01';

        $validator = Validator::make($request->all(), [
            'NUM_PEDIDO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se ingreso Número de Pedido.');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.CAJ_LISTA_DETALLE_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                cTipComp_in => :cTipComp_in,
                cNumComp_in => :cNumComp_in
            );END;");

            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumPedVta_in", $numPedido);
            oci_bind_by_name($stid, ":cTipComp_in", $tipoPago);
            oci_bind_by_name($stid, ":cNumComp_in", $numComprobante);
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
                                'DESCRIPCION' => $datos[1],
                                'UNIDAD' => $datos[2],
                                'PRE_VTA' => $datos[3],
                                'CANTIDAD' => $datos[4],
                                'TOTAL' => $datos[5],
                                'ESPECIALIDAD' => $datos[6]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) == 0) {
                return CustomResponse::failure('No existen información con este número de pedido.');
            }
            
            return CustomResponse::success('Detalles pedido encontrado.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function busquedaOrdenCabecera(Request $request) {
        $numOrden = $request->input('NUM_ORDEN');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $numComprobante = '';
        $tipoPago = '';

        $validator = Validator::make($request->all(), [
            'NUM_ORDEN' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se ingreso Número de Pedido.');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.CAJ_LISTA_CABECERA_ORDEN(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumOrden_in => :cNumOrden_in,
                cNumCompPag => :cNumCompPag,
                cFlagTipProcPago => :cFlagTipProcPago
            );END;");

            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumOrden_in", $numOrden);
            oci_bind_by_name($stid, ":cNumCompPag", $numComprobante);
            oci_bind_by_name($stid, ":cFlagTipProcPago", $tipoPago);
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
                                'FECHA' => $datos[1],
                                'MONTO' => $datos[2],
                                'NUM_DOCUMENTO' => $datos[3],
                                'PACIENTE' => $datos[4],
                                'CAJERO' => $datos[5],
                                'FEC_PENDIENTE' => $datos[6],
                                'FEC_ENVIO_CONSULTA' => $datos[7],
                                'FEC_INI_CONSULTA' => $datos[8],
                                'FEC_FIN_CONSULTA' => $datos[9],
                                '10' => $datos[10],
                                'COD_PACIENTE' => $datos[11],
                                '12' => $datos[12],
                                '13' => $datos[13],
                                'NUM_COMP_PAGO' => $datos[14],
                                'NUM_PEDIDO' => $datos[0],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) == 0) {
                return CustomResponse::failure('No existen información con este número de orden.');
            }
            
            return CustomResponse::success('Cabecera orden encontrado.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function busquedaOrdenDetalles(Request $request) {
        $numOrden = $request->input('NUM_ORDEN');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $numComprobante = '';
        $tipoPago = '01';

        $validator = Validator::make($request->all(), [
            'NUM_ORDEN' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se ingreso Número de Pedido.');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.CAJ_LISTA_DETALLE_ORDEN(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumOrden_in => :cNumOrden_in,
                cTipComp_in => :cTipComp_in,
                cNumComp_in => :cNumComp_in
            );END;");

            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumOrden_in", $numOrden);
            oci_bind_by_name($stid, ":cNumComp_in", $numComprobante);
            oci_bind_by_name($stid, ":cTipComp_in", $tipoPago);
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
                                'DESCRIPCION' => $datos[1],
                                'UNIDAD' => $datos[2],
                                'PRE_VTA' => $datos[3],
                                'CANTIDAD' => $datos[4],
                                'TOTAL' => $datos[5],
                                'ESPECIALIDAD' => $datos[6],
                                '7' => $datos[7]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) == 0) {
                return CustomResponse::failure('No existen información con este número de orden.');
            }
            
            return CustomResponse::success('Cabecera orden encontrado.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function obtenerComprobantesPago() {
        $codGrupoCia = '001';

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_ADMIN_IMP.IMP_LISTA_TIPOS_COMPROBANTE(cCodGrupoCia_in => :cCodGrupoCia_in);END;");
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
                                'descripcion' => $datos[1],
                                'value' => $datos[0]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de comprobantes de pago encontrada.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function obtenerCorrelativoMontoNeto(Request $request) {
        $numComprobante = $request->input('NUM_COMPROBANTE');
        $fecha = $request->input('FECHA');
        $numTipoComp = $request->input('TIPO_COMP_PAGO');
        $codGrupoCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'NUM_COMPROBANTE' => 'required',
            'TIPO_COMP_PAGO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.F_GET_CORRELATIVO_MONTO_NETO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                cTipo_Comp_in => :cTipo_Comp_in,
                cNum_Comp_Pago_in => :cNum_Comp_Pago_in,
                cFechaPedido_in => :cFechaPedido_in);
            end;");
            oci_bind_by_name($stid, ":result", $result, 50, SQLT_CHR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCod_Local_in", $cCodLocal);
            oci_bind_by_name($stid, ":cTipo_Comp_in", $numTipoComp);
            oci_bind_by_name($stid, ":cNum_Comp_Pago_in", $numComprobante);
            oci_bind_by_name($stid, ":cFechaPedido_in", $fecha);
            oci_execute($stid);

            $data = explode(';', $result);

            if (count($data) <= 1) {
                return CustomResponse::failure('No se encontro comprobante.');
            }

            return CustomResponse::success('Lista de comprobantes de pago encontrada.', [
                'NUM_PED_VTA' => $data[0],
                'MONTO' => $data[1],
                'FECHA' => $data[2],
            ]);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function verificarPedido(Request $request) {
        $numPedido = $request->input('NUM_PEDIDO');
        $monto = $request->input('MONTO');
        $reclamo = '';
        $anula = '';
        $valmin = '';
        $codGrupoCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'NUM_PEDIDO' => 'required',
            'MONTO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, "BEGIN PTOVENTA_CAJ_ANUL.CAJ_VERIFICA_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                nMontoVta_in => :nMontoVta_in,
                nIndReclamoNavsat_in => :nIndReclamoNavsat_in,
                cIndAnulaTodoPedido_in => :cIndAnulaTodoPedido_in,
                cValMints_in => :cValMints_in);
            end;");
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumPedVta_in", $numPedido);
            oci_bind_by_name($stid, ":nMontoVta_in", $monto);
            oci_bind_by_name($stid, ":nIndReclamoNavsat_in", $reclamo);
            oci_bind_by_name($stid, ":cIndAnulaTodoPedido_in", $anula);
            oci_bind_by_name($stid, ":cValMints_in", $valmin);
            oci_execute($stid);

            return CustomResponse::success('Verificación exitosa.');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure(substr($th->getMessage(), 26, -91));
        }
    }

    public function obtenerEspecialidadConsultaMedico() {
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $usuLocal = '';

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result :=HHC_LABORATORIO.GET_ESPECIALIDAD(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                cSecUsu_local_in => :cSecUsu_local_in);
            END;");
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCod_Local_in", $cCodLocal);
            oci_bind_by_name($stid, ":cSecUsu_local_in", $usuLocal);
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
                                'value' => $datos[0],
                                'descripcion' => $datos[1]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de especialidades encontrada.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function obtenerConsultorioConsultaMedico(Request $request) {
        $codEspecialidad = $request->input('COD_ESPECIALIDAD');
        $codGrupoCia = '001';
        $cCodLocal = '001';
        $usuLocal = '';

        $validator = Validator::make($request->all(), [
            'COD_ESPECIALIDAD' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Código de especialidad faltante.');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result :=HHC_LABORATORIO.GET_CONSULTORIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                cSecUsu_local_in => :cSecUsu_local_in,
                vIDEspecialidad => :vIDEspecialidad);
            END;");
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCod_Local_in", $cCodLocal);
            oci_bind_by_name($stid, ":cSecUsu_local_in", $usuLocal);
            oci_bind_by_name($stid, ":vIDEspecialidad", $codEspecialidad);
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
                                'value' => $datos[0],
                                'descripcion' => $datos[1]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de especialidades encontrada.', $lista);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function setConfirmarRecepcion(Request $request) {
        $numPedido = $request->input('NUM_PEDIDO');
        $usuCrea = $request->input('USU_CREA');
        $codGrupoCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'NUM_PEDIDO' => 'required',
            'USU_CREA' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, "BEGIN HHC_LABORATORIO.P_CONFIRMA_RECEPCION(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                cUsuCrea_in => :cUsuCrea_in
            );END;");

            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumPedVta_in", $numPedido);
            oci_bind_by_name($stid, ":cUsuCrea_in", $usuCrea);
            oci_execute($stid);

            return CustomResponse::success('Recepción Confirmada. Realice la consulta.');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function insertarAtencionMedica(Request $request) {
        $tipoComp = $request->input('TIPO_COMPROBANTE');
        $numComp = $request->input('NUM_COMPROBANTE');
        $usuCrea = $request->input('USU_CREA');
        $numPedido = $request->input('NUM_PEDIDO');
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $estado = $request->input('ESTADO');
        $codConsulta = '1';
        $codTipoAtencion = $request->input('COD_TIPO_ATENCION');
        $codConsultorio = $request->input('COD_ESPECIALIDAD');
        $numOrden = 'N';
        $codBus = $request->input('COD_BUS');

        $codGrupoCia = '001';
        $cCodLocal = '001';
        $cCodCia = '001';

        $validator = Validator::make($request->all(), [
            'USU_CREA' => 'required',
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_ESPECIALIDAD' => 'required',
            'COD_BUS' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            if ($numPedido) {
                $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.CME_INSERT_ATENCION_MEDICA(
                    cCodGrupoCia_in => :cCodGrupoCia_in,
                    cCodCia_in => :cCodCia_in,
                    cCodLocal_in => :cCodLocal_in,
                    cUsu_in => :cUsu_in,
                    cTipComPago_in => :cTipComPago_in,
                    cNumComPago_in => :cNumComPago_in,
                    cNumPedVta_in => :cNumPedVta_in,
                    cCodLocalVta_in => :cCodLocalVta_in,
                    vCodPaciente => :vCodPaciente,
                    vCodMedico => :vCodMedico,
                    vEstado => :vEstado,
                    vCodConsulta => :vCodConsulta,
                    vCodTipAten => :vCodTipAten,
                    vIdConsultorio_in => :vIdConsultorio_in,
                    vIdBus_in => :vIdBus_in,
                    vOrdenMedica_in => :vOrdenMedica_in
                );end;");
                oci_bind_by_name($stid, ":result", $result, 50, SQLT_CHR);
                oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
                oci_bind_by_name($stid, ":cCodCia_in", $cCodCia);
                oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
                oci_bind_by_name($stid, ":cUsu_in", $usuCrea);
                oci_bind_by_name($stid, ":cTipComPago_in", $tipoComp);
                oci_bind_by_name($stid, ":cNumComPago_in", $numComprobante);
                oci_bind_by_name($stid, ":cNumPedVta_in", $numPedido);
                oci_bind_by_name($stid, ":cCodLocalVta_in", $cCodLocal);
                oci_bind_by_name($stid, ":vCodPaciente", $codPaciente);
                oci_bind_by_name($stid, ":vCodMedico", $codMedico);
                oci_bind_by_name($stid, ":vEstado", $estado);
                oci_bind_by_name($stid, ":vCodConsulta", $codConsulta);
                oci_bind_by_name($stid, ":vCodTipAten", $codTipoAtencion);
                oci_bind_by_name($stid, ":vIdConsultorio_in", $codConsultorio);
                oci_bind_by_name($stid, ":vIdBus_in", $codBus);
                oci_bind_by_name($stid, ":vOrdenMedica_in", $numOrden);
    
                oci_execute($stid);
            } else {
                $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.CME_INSERT_ATENCION_HOSPI(
                    cCodGrupoCia_in => :cCodGrupoCia_in,
                    cCodCia_in => :cCodCia_in,
                    cCodLocal_in => :cCodLocal_in,
                    cUsu_in => :cUsu_in,
                    cTipComPago_in => :cTipComPago_in,
                    cNumComPago_in => :cNumComPago_in,
                    cCodLocalVta_in => :cCodLocalVta_in,
                    vCodPaciente => :vCodPaciente,
                    vCodMedico => :vCodMedico,
                    vEstado => :vEstado,
                    vCodConsulta => :vCodConsulta,
                    vCodTipAten => :vCodTipAten,
                    vIdConsultorio_in => :vIdConsultorio_in,
                    vIdBus_in => :vIdBus_in,
                    vOrdenMedica_in => :vOrdenMedica_in
                );end;");
                oci_bind_by_name($stid, ":result", $result, 50, SQLT_CHR);
                oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
                oci_bind_by_name($stid, ":cCodCia_in", $cCodCia);
                oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
                oci_bind_by_name($stid, ":cUsu_in", $usuCrea);
                oci_bind_by_name($stid, ":cTipComPago_in", $tipoComp);
                oci_bind_by_name($stid, ":cNumComPago_in", $numComprobante);
                oci_bind_by_name($stid, ":cCodLocalVta_in", $cCodLocal);
                oci_bind_by_name($stid, ":vCodPaciente", $codPaciente);
                oci_bind_by_name($stid, ":vCodMedico", $codMedico);
                oci_bind_by_name($stid, ":vEstado", $estado);
                oci_bind_by_name($stid, ":vCodConsulta", $codConsulta);
                oci_bind_by_name($stid, ":vCodTipAten", $codTipoAtencion);
                oci_bind_by_name($stid, ":vIdConsultorio_in", $codConsultorio);
                oci_bind_by_name($stid, ":vIdBus_in", $codBus);
                oci_bind_by_name($stid, ":vOrdenMedica_in", $numOrden);
    
                oci_execute($stid);
            }
            
            return CustomResponse::success('Atención médica registrada.', $result);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th, 'ORA-20019')) return CustomResponse::failure(substr($th->getMessage(), 26, -90));
            return CustomResponse::failure('Error en los servidores');
        }
    }

    public function obtenerTipoConsultaModulos(Request $request) {
        try {
            $modulos = DB::select("select t.llave_tab_gral, t.cod_tab_gral from pbl_tab_gral t where t.cod_apl = 'HHC_LABORATORIO'");
            return CustomResponse::success('Modulos encontrados', $modulos);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function anularConsultaMedica(Request $request) {
        $numAtencion = $request->input('NUM_ATENCION');
        $usuCrea = $request->input('USU_CREA');
        
        $codGrupoCia = '001';
        $cCodCia = '001';
        $cCodLocal = '001';
        $tipoComprobante = '';
        $numComprobante = '';

        $validator = Validator::make($request->all(), [
            'NUM_ATENCION' => 'required',
            'USU_CREA' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.CME_ANULAR_ATENCION_MEDICA(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodCia_in => :cCodCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumAtencion_in => :cNumAtencion_in,
                cUsuario_in => :cUsuario_in,
                cTipComPago_in => :cTipComPago_in,
                cNumComPago_in => :cNumComPago_in
            );end;");

            oci_bind_by_name($stid, ":result", $result, 1, SQLT_CHR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodCia_in", $cCodCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumAtencion_in", $numAtencion);
            oci_bind_by_name($stid, ":cUsuario_in", $usuCrea);
            oci_bind_by_name($stid, ":cTipComPago_in", $tipoComprobante);
            oci_bind_by_name($stid, ":cNumComPago_in", $numComprobante);

            oci_execute($stid);

            return CustomResponse::success('Atención médica anulada.', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function insertarTriaje(Request $request) {
        $numAtencion = $request->input('NUM_ATENCION');
        $usuCrea = $request->input('USU_CREA');

        $nPA1 = $request->input('PA1');
        $nPA2 = $request->input('PA2');
        $nFR = $request->input('FR');
        $nFC = $request->input('FC');
        $nTemp = $request->input('TEMP');
        $nPeso = $request->input('PESO');
        $nTalla = $request->input('TALLA');
        $nSaturacion = $request->input('SATURACION');
        
        $codGrupoCia = '001';
        $cCodCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'NUM_ATENCION' => 'required',
            'USU_CREA' => 'required',
            'PA1' => 'required',
            'PA2' => 'required',
            'FR' => 'required',
            'FC' => 'required',
            'TEMP' => 'required',
            'PESO' => 'required',
            'TALLA' => 'required',
            'SATURACION' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.CME_INSERT_TRIAJE(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodCia_in => :cCodCia_in,
                cCodLocal_in => :cCodLocal_in,
                cUsu_in => :cUsu_in,
                cNumAtenMed_in => :cNumAtenMed_in,
                nPA1_in => :nPA1_in,
                nPA2_in => :nPA2_in,
                nFR_in => :nFR_in,
                nFC_in => :nFC_in,
                nTemp_in => :nTemp_in,
                nPeso_in => :nPeso_in,
                nTalla_in => :nTalla_in,
                nSaturacion_in => :nSaturacion_in
            );end;");

            oci_bind_by_name($stid, ":result", $result, 20, SQLT_CHR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodCia_in", $cCodCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cUsu_in", $usuCrea);
            oci_bind_by_name($stid, ":cNumAtenMed_in", $numAtencion);
            oci_bind_by_name($stid, ":nPA1_in", $nPA1);
            oci_bind_by_name($stid, ":nPA2_in", $nPA2);
            oci_bind_by_name($stid, ":nFR_in", $nFR);
            oci_bind_by_name($stid, ":nFC_in", $nFC);
            oci_bind_by_name($stid, ":nTemp_in", $nTemp);
            oci_bind_by_name($stid, ":nPeso_in", $nPeso);
            oci_bind_by_name($stid, ":nTalla_in", $nTalla);
            oci_bind_by_name($stid, ":nSaturacion_in", $nSaturacion);

            oci_execute($stid);

            return CustomResponse::success('Triaje generado correctamente.', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function actualizarSolicitudAtencion(Request $request) {
        $numAtencion = $request->input('NUM_ATENCION');
        $usuCrea = $request->input('USU_CREA');
        $codEstado = $request->input('ESTADO');
        $codGrupoCia = '001';
        $cCodLocal = '001';

        $validator = Validator::make($request->all(), [
            'NUM_ATENCION' => 'required',
            'USU_CREA' => 'required',
            'ESTADO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, "BEGIN CENTRO_MEDICO.F_UPDATE_SOLICITUD_ATENCION(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumAtencion_in => :cNumAtencion_in,
                cCodEstadoNew_in => :cCodEstadoNew_in,
                cUsuario_in => :cUsuario_in
            );end;");

            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal);
            oci_bind_by_name($stid, ":cNumAtencion_in", $numAtencion);
            oci_bind_by_name($stid, ":cCodEstadoNew_in", $codEstado);
            oci_bind_by_name($stid, ":cUsuario_in", $usuCrea);

            oci_execute($stid);

            return CustomResponse::success('Atención actualizado correctamente.');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure('Error en los servidores.');
        }
    }
}


function unique_multidim_array($array, $key)
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}

function setDatosSugerencias($imagenes, $laboratorios, $tratamientos, $interconsultas, $procedimientos, $idDiagnostico, $cod_medico)
{

    // SETEAMOS LAS IMAGENES                    
    foreach ($imagenes as $key => $imagen) {

        $existe = SugImagen::query()
            ->where(['key' => $imagen['key']])->first();

        if (!$existe) {
            SugImagen::insert([
                'key' => $imagen['key'],
                'COD_PROD' => $imagen['COD_PROD'],
                'DESC_PROD' => $imagen['DESC_PROD'],
                'NOM_LAB' => $imagen['NOM_LAB'],
                'RUC' => $imagen['RUC']
            ]);
        }

        // SETEAMOS EL DETALLE DEL DIAGNOSTICO

        $fecha = new DateTime();

        SugerenciaDetalle::insert([
            'key' => $imagen['key'],
            'tipoSugerencia' => 'imagen',
            'codDiagnostico' => $idDiagnostico,
            'cod_medico' => $cod_medico,
            'idDetalleSugerencia' => $fecha->getTimestamp() + $key + rand(1, 1000),
        ]);
    }

    // SETEAMOS LOS LABORATORIOS
    foreach ($laboratorios as $key => $laboratorio) {

        $existe = SugLaboratorio::query()
            ->where(['key' => $laboratorio['key']])->first();

        if (!$existe) {

            SugLaboratorio::insert([
                'key' => $laboratorio['key'],
                'COD_PROD' => $laboratorio['COD_PROD'],
                'DESC_PROD' => $laboratorio['DESC_PROD'],
                'NOM_LAB' => $laboratorio['NOM_LAB'],
                'RUC' => $laboratorio['RUC']
            ]);
        }

        // SETEAMOS EL DETALLE DEL DIAGNOSTICO

        $fecha = new DateTime();

        SugerenciaDetalle::insert([
            'key' => $laboratorio['key'],
            'tipoSugerencia' => 'laboratorio',
            'cod_medico' => $cod_medico,
            'codDiagnostico' => $idDiagnostico,
            'idDetalleSugerencia' => $fecha->getTimestamp() + $key + rand(1, 1000),
        ]);
    }

    // SETEAMOS LOS TRATAMIENTOS
    foreach ($tratamientos as $key => $tratamiento) {

        $existe = SugTratamiento::query()
            ->where(['key' => $tratamiento['key']])->first();

        if (!$existe) {
            SugTratamiento::insert([
                'key' => $tratamiento['key'],
                'cantidad' => $tratamiento['cantidad'],
                'codprod' => $tratamiento['codprod'],
                'rucempresa' => $tratamiento['rucempresa'],
                'valfrac' => $tratamiento['valfrac'],
                'unidvta' => $tratamiento['unidvta'],
                'viaadministracion' => $tratamiento['viaadministracion'],
                'etiquetaVia' => $tratamiento['etiquetaVia'],
                'frecuencia' => $tratamiento['frecuencia'],
                'duracion' => $tratamiento['duracion'],
                'dosis' => $tratamiento['dosis'],
                'recomendacionAplicar' => $tratamiento['recomendacionAplicar'],
                'tratamiento' => $tratamiento['tratamiento']
            ]);
        }

        // SETEAMOS EL DETALLE DEL DIAGNOSTICO

        $fecha = new DateTime();

        SugerenciaDetalle::insert([
            'key' => $tratamiento['key'],
            'tipoSugerencia' => 'tratamiento',
            'codDiagnostico' => $idDiagnostico,
            'cod_medico' => $cod_medico,
            'idDetalleSugerencia' => $fecha->getTimestamp() + $key + rand(1, 1000),
        ]);
    }

    // SETEAMOS LAS INTERCONSULTAS
    foreach ($interconsultas as $key => $interconsulta) {

        $existe = SugInterconsulta::query()
            ->where(['key' => $interconsulta['key']])->first();

        if (!$existe) {
            SugInterconsulta::insert([
                'key' => $interconsulta['key'],
                'COD_PROD' => $interconsulta['COD_PROD'],
                'DESC_PROD' => $interconsulta['DESC_PROD'],
                'NOM_LAB' => $interconsulta['NOM_LAB'],
                'RUC' => $interconsulta['RUC'],
            ]);
        }

        // SETEAMOS EL DETALLE DEL DIAGNOSTICO

        $fecha = new DateTime();

        SugerenciaDetalle::insert([
            'key' => $interconsulta['key'],
            'tipoSugerencia' => 'interconsulta',
            'codDiagnostico' => $idDiagnostico,
            'cod_medico' => $cod_medico,
            'idDetalleSugerencia' => $fecha->getTimestamp() + $key + rand(1, 1000),
        ]);
    }

    // SETEAMOS LOS PROCEDIMIENTOS
    foreach ($procedimientos as $key => $procedimiento) {

        $existe = SugProcedimiento::query()
            ->where(['key' => $procedimiento['key']])->first();

        if (!$existe) {

            SugProcedimiento::insert([
                'key' => $procedimiento['key'],
                'COD_PROD' => $procedimiento['COD_PROD'],
                'DESC_PROD' => $procedimiento['DESC_PROD'],
                'NOM_LAB' => $procedimiento['NOM_LAB'],
                'RUC' => $procedimiento['RUC']
            ]);
        }

        // SETEAMOS EL DETALLE DEL DIAGNOSTICO

        $fecha = new DateTime();

        SugerenciaDetalle::insert([
            'key' => $procedimiento['key'],
            'tipoSugerencia' => 'procedimiento',
            'codDiagnostico' => $idDiagnostico,
            'cod_medico' => $cod_medico,
            'idDetalleSugerencia' => $fecha->getTimestamp() + $key + rand(1, 1000),
        ]);
    }
}

function convercion($abc)
{
    $resultado = explode('.', strval($abc));

    if (count($resultado) > 1) {
        $resultado = $resultado[0] . ',' . $resultado[1];
        return $resultado;
    }
    return $abc;
}
