<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Procedimientos;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProcedimientosController extends Controller
{
    /**
     * Obtener lista de procedimientos
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/procedimientos/getProcedimientos",
     *     tags={"Procedimientos"},
     *     operationId="comboProcedimientos",
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
                $model = Procedimientos::where(['COD_GRUPO_CIA' => $codGrupoCia, 'COD_PACIENTE' => $codPaciente, 'COD_MEDICO' => $codMedico, 'NRO_ATENCION' => $nroAtencion])->get();
                if ($model) {
                    return CustomResponse::success('Procedimientos encontrados', $model);
                } else {
                    return CustomResponse::failure('No se encuentran procedimientos');
                }
            } catch (Exception $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }
}
