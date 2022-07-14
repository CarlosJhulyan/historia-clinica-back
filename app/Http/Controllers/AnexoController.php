<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Anexo;
use App\Oracle\OracleDB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnexoController extends Controller
{
    /**
     * Grabar los Anexos
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/grabarAnexos/GrabarAnexos",
     *     tags={"Anexos"},
     *     operationId="grabarAnexos",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *               required={
     *                  "codGrupoCia",
     *                  "codCia",
     *                  "codLocal",
     *                  "obsAnexo",
     *                  "codMedico",
     *                  "numAtendMed"
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
     *                     property="obsAnexo",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",
     *     )
     * )
     */
    public function GrabarAnexos(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codCia      = $request->input('codCia');
        $codLocal    = $request->input('codLocal');
        $obsAnexo       = $request->input('obsAnexo');
        $codMedico       = $request->input('codMedico');
        $numAtendMed = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codCia'      => 'required',
            'codLocal'    => 'required',
            'obsAnexo'       => 'required',
            'codMedico'       => 'required',
            'numAtendMed' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $conn = OracleDB::getConnection();
                $nombreImagen = $request->imagen->getClientOriginalName();
                $extFile = $request->imagen->extension();
                $request->imagen->move(public_path('imagenes/Anexo/' . $codMedico), $nombreImagen);
                $rutaImagen = 'imagenes/Anexo/' . $codMedico . '/' . $nombreImagen;
                $stid = oci_parse($conn, "BEGIN :result := HHSUR_CME_FILE_ANEXO.F_GRABAR_ANEXOS( :codgrupocia,  :codcia, :codlocal,  :anexo, :rutalocal, :rutaservidor, :nomfile , :extfile,  :idusu,  :munatenmed); END;");
                oci_bind_by_name($stid, ":result", $resultado, 4000);
                oci_bind_by_name($stid, ":codgrupocia", $codGrupoCia);
                oci_bind_by_name($stid, ":codcia", $codCia);
                oci_bind_by_name($stid, ":codlocal", $codLocal);
                oci_bind_by_name($stid, ":anexo", $obsAnexo);
                oci_bind_by_name($stid, ":rutalocal", $rutaImagen);
                oci_bind_by_name($stid, ":rutaservidor", $rutaImagen);
                oci_bind_by_name($stid, ":nomfile", $nombreImagen);
                oci_bind_by_name($stid, ":extfile", $extFile);
                oci_bind_by_name($stid, ":idusu", $codMedico);
                oci_bind_by_name($stid, ":munatenmed", $numAtendMed);
                oci_execute($stid);
                oci_free_statement($stid);
                oci_close($conn);
                return CustomResponse::success();
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener los Anexos
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/anexos/getAnexos",
     *     tags={"Anexos"},
     *     operationId="getAnexos",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="numAtendMed",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "numAtendMed": "0000384457"
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
    public function getAnexos(Request $request)
    {
        $NUM_ATEN_MED = $request->input('numAtendMed');
        $validator = Validator::make($request->all(), [
            'numAtendMed' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $model = Anexo::where(['EST_ANEXO' => 'A', 'NUM_ATEN_MED' => $NUM_ATEN_MED])
                    ->get();
                if ($model) {
                    return CustomResponse::success('Anexos encontrados', $model);
                } else {
                    return CustomResponse::failure('No se encuentran anexos');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Obtener los Anexos por rango de fechas
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/anexos/getAnexosFecha",
     *     tags={"Anexos"},
     *     operationId="getAnexosFecha",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "numAtendMed"
     *               },
     *                 @OA\Property(
     *                     property="numAtendMed",
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
     *                  "numAtendMed": "0000384457",
     *                  "fechaInicio": "2021-12-01",
     *                  "fechaFin": "2021-12-15"
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
    public function getAnexosFecha(Request $request)
    {
        $NUM_ATEN_MED = $request->input('numAtendMed');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $validator = Validator::make($request->all(), [
            'numAtendMed' => 'required'
        ]);
        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $model = Anexo::where(['EST_ANEXO' => 'A', 'NUM_ATEN_MED' => $NUM_ATEN_MED])->where('FEC_CREA', '>=', $fechaInicio)->where('FEC_CREA', '<=', $fechaFin)
                    ->get();
                if ($model) {
                    return CustomResponse::success('Anexos encontrados', $model);
                } else {
                    return CustomResponse::failure('No se encuentran anexos');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    /**
     * Borrar Anexo
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/anexos/deleteAnexos",
     *     tags={"Anexos"},
     *     operationId="deleteAnexos",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *               required={
     *                  "codAnexo"
     *               },
     *                 @OA\Property(
     *                     property="codAnexo",
     *                     type="string"
     *                 ),
     *                 example={
     *                  "codAnexo": "0000000001"
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
    public function deleteAnexos(Request $request)
    {
        $COD_ANEXO = $request->input('codAnexo');
        $validator = Validator::make($request->all(), [
            'codAnexo' => 'required'
        ]);
        if ($validator) {
            try {
                $estado = 'I';
                DB::update("update CME_ATENCION_MEDICA_ANEXO set EST_ANEXO = ? where COD_ANEXO = ?", [$estado, $COD_ANEXO]);
                return CustomResponse::success('Registro desactivado');
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
/**
     * Obtener los tipos de anexos
     *
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/tipoAnexos",
     *     tags={"Anexos"},
     *     operationId="tipoAnexos",
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",
     *     )
     * )
     */
    public function TipoAnexos(Request $request)
    {
        try {
            $anexos = DB::select("select * from pbl_tab_gral where ID_TAB_GRAL = '1001'");
            return CustomResponse::success('Datos encontrados', $anexos);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    public function getThemeDesign() {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := HHC_LOOK_AND_FEEL.F_GET_DESIGN_HHC;end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
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
                                'ID_TAB_GRAL' => $datos[0],
                                'COMPANIA' => $datos[1],
                                'LOGO' => $datos[2],
                                'COD_COLOR_1' => $datos[3],
                                'COD_COLOR_2' => $datos[4],
                                'COD_COLOR_3' => $datos[5],
                            ]
                        );
                    }
                }
            }
            oci_close($conn);
            if (count($lista) > 1) return CustomResponse::failure('El sistema detectó más de un tema habilitado');
            return CustomResponse::success('Datos encontrados', $lista[0]);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }
}
