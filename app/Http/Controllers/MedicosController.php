<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use Illuminate\Http\Request;
use App\Models\DatosFirmas;
use Illuminate\Support\Facades\Validator;

class MedicosController extends Controller
{

    /**
     * Grabar los datos de firma
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/medicos/DatosFirmas",
     *     tags={"Medicos"},
     *     operationId="medicosDatosFirmas",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={
     *                  "codMedi",
     *                  "fechaFirma",
     *                  "imagen",
     *               },
     *                 @OA\Property(
     *                     property="codMedi",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="fechaFirma",
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
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    public function DatosFirmas(Request $request)
    {
        $codMedi = $request->input('codMedi');
        $fechaFirma = $request->input('fechaFirma');
        $validator = Validator::make($request->all(), [
            'codMedi' => 'required',
            'fechaFirma' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {

                $imagenFirma = $codMedi . '.' . $request->imagen->extension();
                $request->imagen->move(public_path('imagenes/'), $imagenFirma);

                $registroFirma = [
                    'COD_MED' => $codMedi,
                    'URL_FIRMA' => 'imagenes/' . $imagenFirma,
                    'FECHA_FIRMAS' => $fechaFirma,
                    'ESTADO' => '1'
                ];

                DatosFirmas::insert($registroFirma);

                return CustomResponse::success('Datos encontrados.', $registroFirma);
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener firma
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/medicos/getFirma",
     *     tags={"Medicos"},
     *     operationId="medicosGetFirma",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codMedico"
     *               },
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
    public function getFirma(Request $request)
    {
        $codMedico = $request->input('codMedico');
        $validator = Validator::make($request->all(), [
            'codMedico' => 'required',
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $model = DatosFirmas::where(['ESTADO' => '1', 'COD_MED' => $codMedico])->first();
                if ($model) {
                    return CustomResponse::success('Firma encontrada', $model);
                } else {
                    return CustomResponse::failure('No se encuentra la firma');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
}
