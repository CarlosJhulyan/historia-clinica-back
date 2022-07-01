<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Firma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DatosFirmasController extends Controller
{
    /**
     * Obtener Firmas
     *
     * @OA\Get(
     *     path="/historial-clinico-backend/public/api/firmas/getFirmas",
     *     tags={"Firmas"},
     *     operationId="getFirmas",
     *     @OA\Response(
     *         response=200,
     *         description="Firmas Encontradas",
     *     )
     * )
     */
    public function getFirmas()
    {
        try {
            $model = Firma::select("HCW_FIRMAS.*", "MAE_MEDICO.DES_NOM_MEDICO as NOMBRES", "MAE_MEDICO.DES_APE_MEDICO as APELLIDOS")
                ->where(['ESTADO' => "1"])
                ->join('MAE_MEDICO', 'COD_MEDICO', '=', 'COD_MED')
                ->get();
            return CustomResponse::success("Firmas Encontradas", $model);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    /**
     * Obtener Firma de medico
     *
     * @OA\Get(
     *     path="/historial-clinico-backend/public/api/firmas/getFirma",
     *     tags={"Firmas"},
     *     operationId="getFirma",
     *     @OA\Response(
     *         response=200,
     *         description="Firmas Encontradas",
     *     )
     * )
     */
    public function getFirma(Request $request)
    {
        $cod_med = $request->input('cod_med');
        $validator = Validator::make($request->all(), [
            'cod_med' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $model = Firma::select("*")
                ->where(['cod_med' => $cod_med])
                ->get();
            return CustomResponse::success("Firma Encontrada", $model);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    /**
     * Grabar una firma
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/firmas/createFirma",
     *     tags={"Firmas"},
     *     operationId="createDatosFirmas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={
     *                  "cod_med",
     *                  "imagen",
     *               },
     *                 @OA\Property(
     *                     property="cod_med",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Firma registrada",
     *     )
     * )
     */
    public function createFirma(Request $request)
    {
        $cod_med = $request->input('cod_med');
        $validator = Validator::make($request->all(), [
            'cod_med' => 'required',
            'imagen' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $fechaFirma = new \DateTime();
                $fechaFirma->format('Y-m-d');
                $imagenFirma = $cod_med . '.' . $request->imagen->extension();
                $request->imagen->move(public_path('imagenes/'), $imagenFirma);
                $firma = [
                    'COD_MED' => $cod_med,
                    'URL_FIRMA' => 'imagenes/' . $imagenFirma,
                    'FECHA_FIRMA' => $fechaFirma,
                    'ESTADO' => '1'
                ];
                Firma::insert($firma);
                return CustomResponse::success('Firma registrada', $firma);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Actualizar una firma
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/firmas/updateFirma",
     *     tags={"Firmas"},
     *     operationId="updateDatosFirmas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={
     *                  "cod_med",
     *                  "imagen",
     *               },
     *                 @OA\Property(
     *                     property="cod_med",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Firma actualizada",
     *     )
     * )
     */
    public function updateFirma(Request $request)
    {
        $cod_med = $request->input('cod_med');
        $validator = Validator::make($request->all(), [
            'cod_med' => 'required',
            'imagen' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $fechaFirma = new \DateTime();
                $fechaFirma->format('Y-m-d');
                $imagenFirma = $cod_med . '.' . $request->imagen->extension();
                $request->imagen->move(public_path('imagenes/'), $imagenFirma);
                DB::update(
                    "UPDATE HCW_FIRMAS SET URL_FIRMA = ? , FECHA_FIRMA = ?, ESTADO = ? WHERE COD_MED = ?",
                    ['imagenes/' . $imagenFirma, $fechaFirma, '1', $cod_med]
                );
                return CustomResponse::success('Firma actualizada');
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Eliminar una firma
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/firmas/deleteFirma",
     *     tags={"Firmas"},
     *     operationId="deleteDatosFirmas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={
     *                  "cod_med"
     *               },
     *                 @OA\Property(
     *                     property="cod_med",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Firma eliminada",
     *     )
     * )
     */
    public function deleteFirma(Request $request)
    {
        $cod_med = $request->input('cod_med');
        $validator = Validator::make($request->all(), [
            'cod_med' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                DB::update(
                    "UPDATE HCW_FIRMAS SET ESTADO = ? WHERE COD_MED = ?",
                    ['0', $cod_med]
                );
                return CustomResponse::success('Firma eliminada');
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
}
