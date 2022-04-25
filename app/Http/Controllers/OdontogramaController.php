<?php

namespace App\Http\Controllers;

use App\Core\Constants;
use App\Core\CustomResponse;
use App\Models\DatosOdontograma;
use App\Models\OdontogramaHistorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use DateTime;


class OdontogramaController extends Controller
{
    public function eliminar(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                DatosOdontograma::where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])->delete();
            } catch (Exception $e) {
                error_log($e);
                return CustomResponse::failure('Ocurrió un error al eliminar');
            }
        }
    }

    /**
     * Registrar odontograma
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/odontograma/registrar",
     *     tags={"Odontograma"},
     *     operationId="odontogramaRegistrar",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "data",
     *                  "COD_PACIENTE",
     *                  "COD_MEDICO",
     *                  "COD_GRUPO_CIA",
     *                  "DETALLES",
     *                  "OBSERVACIONES",
     *                  "ESPECIFICACIONES"
     *               },
     *                 @OA\Property(
     *                     property="COD_PACIENTE",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_MEDICO",
     *                     type="string",
     *                     example="0000026144",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_GRUPO_CIA",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="DETALLES",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="OBSERVACIONES",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="ESPECIFICACIONES",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="data",
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
    public function registrar(Request $request): JsonResponse
    {
        $data = $request->input('data');
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $detalles = $request->input('DETALLES');
        $observaciones = $request->input('OBSERVACIONES');
        $especificaciones = $request->input('ESPECIFICACIONES');
        $validator = Validator::make($request->all(), [
            'data' => 'required',
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
            'DETALLES' => 'required',
            'OBSERVACIONES' => 'required',
            'ESPECIFICACIONES' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $id = round(((microtime(true)) * 1000)) . 'OH' . uniqid();
                $registroHistorial = [
                    'ID_HISTORIAL' => $id,
                    'COD_PACIENTE' => $codPaciente,
                    'COD_GRUPO_CIA' => $codGrupoCia,
                    'COD_MEDICO' => $codMedico,
                    'DETALLES' => $detalles,
                    'FECHA' => date('Y-m-d H:i:s'),
                    'OBSERVACIONES' => $observaciones,
                    'ESPECIFICACIONES' => $especificaciones,
                ];
                OdontogramaHistorial::insert($registroHistorial);
                foreach (Constants::LISTA_OPCIONES as $opcion) {
                    foreach ($this->filtrarRegistro($request, $opcion, $id) as $row) {
                        DatosOdontograma::insert($row);
                    }
                }
                return CustomResponse::success();
            } catch (Exception $e) {
                error_log($e);
                echo $e;
                return CustomResponse::failure('Ocurrió un error al registrar');
            }
        }
    }

    private function filtrarRegistro($request, $tipo, $idOdontogramaHistorial): array
    {
        $result = [];
        $data = $request->input('data');
        if (array_key_exists($tipo, $data)) {
            $lista = $data[$tipo];
            if (is_array($lista)) {
                foreach ($lista as $index => $diente) {
                    $id = round(((microtime(true)) * 1000)) . $index . uniqid();
                    $registro = [
                        'ID_DATOS' => $id,
                        'ID_OPCIONES' => $tipo,
                        'ID_HISTORIAL' => $idOdontogramaHistorial
                    ];
                    $registro['DIAGNOSTICO'] = array_key_exists('diagnostico', $diente) ? $diente['diagnostico'] : null;
                    $registro['ESTADO'] = array_key_exists('estado', $diente) ? $diente['estado'] : null;
                    if (array_key_exists('inicio', $diente)) {
                        $registro['DIENTE'] = $diente['inicio'];
                    } else {
                        $registro['DIENTE'] = $diente['diente'] ?? 0;
                    }
                    $registro['DIENTE_FIN'] = array_key_exists('fin', $diente) ? $diente['fin'] : null;
                    $registro['TIPO'] = array_key_exists('tipo', $diente) ? $diente['tipo'] : null;
                    $registro['PARTES'] = array_key_exists('partes', $diente) ? json_encode($diente['partes']) : null;
                    array_push($result, $registro);
                }
                return $result;
            }
        }
        return [];
    }

    /**
     * Obtener el odontograma inicial
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/odontograma/inicial",
     *     tags={"Odontograma"},
     *     operationId="odontogramaInicial",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "COD_PACIENTE",
     *                  "COD_MEDICO",
     *                  "COD_GRUPO_CIA"
     *               },
     *                 @OA\Property(
     *                     property="COD_PACIENTE",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_MEDICO",
     *                     type="string",
     *                     example="0000026144",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_GRUPO_CIA",
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
    public function getOdontogramaInicial(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = OdontogramaHistorial::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])
                    ->orderBy('fecha', 'ASC')->first();
                if ($datos) {
                    $rawDatos = DatosOdontograma::where(['id_historial' => $datos['id_historial']])->get();
                    $respuesta = [];
                    foreach (Constants::LISTA_OPCIONES as $opcion) {
                        $respuesta[$opcion] = $this->filtrarSalida($rawDatos, $opcion);
                    }
                    $datos['datosOdontograma'] = $respuesta;
                    return CustomResponse::success('Datos encontrados', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con un odontograma registrado',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                echo $e;
                error_log($e);
                return CustomResponse::failure('Ocurrió un error al obtener los datos');
            }
        }
    }

    /**
     * Obtener el odontograma final
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/odontograma/final",
     *     tags={"Odontograma"},
     *     operationId="odontogramaFinal",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "COD_PACIENTE",
     *                  "COD_MEDICO",
     *                  "COD_GRUPO_CIA"
     *               },
     *                 @OA\Property(
     *                     property="COD_PACIENTE",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_MEDICO",
     *                     type="string",
     *                     example="0000026144",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_GRUPO_CIA",
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
    public function getOdontogramaFinal(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = OdontogramaHistorial::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])
                    ->orderBy('fecha', 'DESC')->first();
                if ($datos) {
                    $rawDatos = DatosOdontograma::where(['id_historial' => $datos['id_historial']])->get();
                    $respuesta = [];
                    foreach (Constants::LISTA_OPCIONES as $opcion) {
                        $respuesta[$opcion] = $this->filtrarSalida($rawDatos, $opcion);
                    }
                    $datos['datosOdontograma'] = $respuesta;
                    return CustomResponse::success('Datos encontrados', $datos);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con un odontograma registrado',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                echo $e;
                error_log($e);
                return CustomResponse::failure('Ocurrió un error al obtener los datos');
            }
        }
    }

    /**
     * Obtener el historial entre fechas
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/odontograma/historial/fecha",
     *     tags={"Odontograma"},
     *     operationId="odontogramaHistorialFecha",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "COD_PACIENTE",
     *                  "COD_GRUPO_CIA",
     *                  "FECHA_INICIO",
     *                  "FECHA_FIN"
     *               },
     *                 @OA\Property(
     *                     property="COD_PACIENTE",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_GRUPO_CIA",
     *                     type="string",
     *                     example="001",
     *                 ),
     *                 @OA\Property(
     *                     property="FECHA_INICIO",
     *                     type="string",
     *                     example="2021-12-20",
     *                 ),
     *                 @OA\Property(
     *                     property="FECHA_FIN",
     *                     type="string",
     *                     example="2021-12-22",
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
    public function getHistorialEntreFechas(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        // $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $fechaInicio = $request->input('FECHA_INICIO');
        $fechaFin = $request->input('FECHA_FIN');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            // 'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
            'FECHA_INICIO' => 'required|date_format:Y-m-d',
            'FECHA_FIN' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = OdontogramaHistorial::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia])->select()
                    ->join('MAE_MEDICO', 'MAE_MEDICO.COD_MEDICO', '=', 'HCW_ODONTOGRAMA_HISTORIAL.COD_MEDICO')
                    ->select('HCW_ODONTOGRAMA_HISTORIAL.*', 'MAE_MEDICO.DES_NOM_MEDICO', 'MAE_MEDICO.DES_APE_MEDICO')
                    ->whereBetween('HCW_ODONTOGRAMA_HISTORIAL.fecha', [$fechaInicio, $fechaFin])
                    ->orderBy('HCW_ODONTOGRAMA_HISTORIAL.FECHA', 'DESC')
                    ->get();

                return CustomResponse::success('Datos encontrados', $datos);
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

    /**
     * Obtener el historial
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/odontograma/historial",
     *     tags={"Odontograma"},
     *     operationId="odontogramaHistorial",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "COD_PACIENTE",
     *                  "COD_GRUPO_CIA"
     *               },
     *                 @OA\Property(
     *                     property="COD_PACIENTE",
     *                     type="string",
     *                     example="0010185756",
     *                 ),
     *                 @OA\Property(
     *                     property="COD_GRUPO_CIA",
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
    public function getHistorial(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'COD_GRUPO_CIA' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = OdontogramaHistorial::query()
                    ->where(['COD_PACIENTE' => $codPaciente, 'COD_GRUPO_CIA' => $codGrupoCia])->select()
                    ->join('MAE_MEDICO', 'MAE_MEDICO.COD_MEDICO', '=', 'HCW_ODONTOGRAMA_HISTORIAL.COD_MEDICO')
                    ->select('HCW_ODONTOGRAMA_HISTORIAL.*', 'MAE_MEDICO.DES_NOM_MEDICO', 'MAE_MEDICO.DES_APE_MEDICO')
                    ->orderBy('HCW_ODONTOGRAMA_HISTORIAL.FECHA', 'DESC')
                    ->get();
                return CustomResponse::success('Datos encontrados', $datos);
            } catch (Exception $e) {
                echo $e;
                error_log($e);
                return CustomResponse::failure('Ocurrió un error al obtener los datos');
            }
        }
    }

    /**
     * Obtener el detalle de un historial
     * 
     * @OA\Get(
     *     path="/historial-clinico-backend/public/api/odontograma/detalle/{idHistorial}",
     *     tags={"Odontograma"},
     *     operationId="odontogramaDetalleHistorial",
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function getDetalle($idHistorial): JsonResponse
    {
        try {
            $datos = OdontogramaHistorial::query()->find($idHistorial);
            if ($datos) {
                $rawDatos = DatosOdontograma::where(['id_historial' => $datos['id_historial']])->get();
                $respuesta = [];
                foreach (Constants::LISTA_OPCIONES as $opcion) {
                    $respuesta[$opcion] = $this->filtrarSalida($rawDatos, $opcion);
                }
                $datos['datosOdontograma'] = $respuesta;
                return CustomResponse::success('Datos encontrados', $datos);
            } else {
                return CustomResponse::failure('No se encuentra el detalle.');
            }
        } catch (Exception $e) {
            echo $e;
            error_log($e);
            return CustomResponse::failure('Ocurrió un error al obtener los datos');
        }
    }

    public function getOdontograma(Request $request): JsonResponse
    {
        $codPaciente = $request->input('COD_PACIENTE');
        $codMedico = $request->input('COD_MEDICO');
        $codGrupoCia = $request->input('COD_GRUPO_CIA');
        $validator = Validator::make($request->all(), [
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'COD_GRUPO_CIA' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $datos = DatosOdontograma::where(['COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'COD_GRUPO_CIA' => $codGrupoCia])->get();
                if (count(($datos ?? [])) > 0) {
                    $respuesta = [];
                    foreach (Constants::LISTA_OPCIONES as $opcion) {
                        $respuesta[$opcion] = $this->filtrarSalida($datos, $opcion);
                    }
                    return CustomResponse::success('Datos encontrados', $respuesta);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'El paciente no cuenta con un odontograma registrado',
                        'data' => null,
                    ]);
                }
            } catch (Exception $e) {
                error_log($e);
                return CustomResponse::failure('Ocurrió un error al obtener los datos');
            }
        }
    }

    private function filtrarSalida($datosOdontograma, $tipo): array
    {
        $result = [];
        foreach ($datosOdontograma as $diente) {
            if ($diente['id_opciones'] == $tipo) {
                $respuesta = [];
                if ($diente['tipo']) {
                    $respuesta['tipo'] = $diente['tipo'];
                }
                if ($diente['partes']) {
                    $respuesta['partes'] = json_decode($diente['partes']);
                }
                $respuesta['diente'] = intval($diente['diente']);
                if ($diente['diagnostico']) {
                    $respuesta['diagnostico'] = $diente['diagnostico'];
                }
                if ($diente['estado']) {
                    $respuesta['estado'] = intval($diente['estado']);
                }
                if ($diente['diente_fin']) {
                    $respuesta['fin'] = intval($diente['diente_fin']);
                }
                if ($diente['enlaces']) {
                    $respuesta['enlaces'] = json_decode($diente['enlaces']);
                }
                array_push($result, $respuesta);
            }
        }
        return $result;
    }
}
