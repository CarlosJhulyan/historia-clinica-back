<?php

namespace App\Http\Controllers;


use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Obtener las Especialidades
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/admin/getEspecialidades",
     *     tags={"Administrador"},
     *     operationId="getEspecialidades",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="codGrupoCia",
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
     *                 example={"codGrupoCia": "001", "codLocal": "001", "codMedico": "0000026144"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    function getEspecialidades(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codMedico = $request->input('codMedico');


        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codMedico' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $todos = 'VT';

            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := HHC_LABORATORIO.F_CUR_LISTA_ESP( :codGrupoCia, :codLocal, :codMedico, :todos ); END;");
            oci_bind_by_name($stid, ":codGrupoCia",  $codGrupoCia);
            oci_bind_by_name($stid, ":codLocal",  $codLocal);
            oci_bind_by_name($stid, ":codMedico",  $codMedico);
            oci_bind_by_name($stid, ":todos",  $todos);
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
            return CustomResponse::success('Tabla Especialidad encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error: ' . $th->getMessage());
        }
    }

    /**
     * Obtener lista de Atenciones
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/admin/getListaAtenciones",
     *     tags={"Administrador"},
     *     operationId="getListaAtenciones",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="codGrupoCia",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="codMedico",
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
     *                 example={"codGrupoCia": "001", "codMedico": "0000026144", "fechaInicio": "01/11/2021", "fechaFin": "01/12/2021"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    function getListaAtenciones(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $codMedico = $request->input('codMedico');


        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'fechaInicio' => 'required',
            'fechaFin' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {

            $todos = '';

            if ($codMedico == "") {
                $codMedico = "";
                $todos = "VT";
            }


            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.F_LISTA_TRAZABILIDAD( :codGrupoCia, :fechaInicio, :fechaFin, :codMedico, :todos ); END;");
            oci_bind_by_name($stid, ":codGrupoCia",  $codGrupoCia);
            oci_bind_by_name($stid, ":fechaInicio",  $fechaInicio);
            oci_bind_by_name($stid, ":fechaFin",  $fechaFin);
            oci_bind_by_name($stid, ":codMedico",  $codMedico);
            oci_bind_by_name($stid, ":todos",  $todos);
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);

            $lista = [];
            if ($stid) {

                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    foreach ($row as $key => $value) {

                        $datos = explode('Ã', $value);
                        $keys = explode("||'Ã'||", $key);

                        $abc = [
                            "key" => $datos[18] . $datos[17],
                            "FEC_CREA" => $datos[0],
                            "FEC_CREA_HORA" => $datos[1],
                            "NRO_HC_ACTUAL" => $datos[2],
                            "NOMBRE" => $datos[3],
                            "EDAD" => $datos[4],
                            "ESTADO" => $datos[5],
                            "DESCRIPCION" => $datos[6],
                            "MEDICO" => $datos[7],
                            "NVL_COD_PACIENTE" => $datos[8],
                            "COD_ESTADO" => $datos[9],
                            "NVL_NRO_HC_FISICA" => $datos[10],
                            "NRO_HC_FISICA" => $datos[11],
                            "NVL_NUM_ATEN_MED" => $datos[12],
                            "IND_ANULADO" => $datos[13],
                            "COD_GRUPO_CIA" => $datos[14],
                            "COD_CIA" => $datos[15],
                            "COD_LOCAL" => $datos[16],
                            "NUM_ATEN_MED" => $datos[17],
                            "COD_PACIENTE" => $datos[18],
                            "COD_MEDICO" => $datos[19],
                            "ID_CONSULTORIO" => $datos[20],
                            "ESPECIALIDAD" => $datos[21],
                            "NUM_ORDEN_VTA" => $datos[22]
                        ];

                        $lista[] = $abc;
                    }
                }
            }
            return CustomResponse::success('Tabla Lista Atenciones encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error: ' . $th->getMessage());
        }
    }

     /**
     * Obtener lista de Liberados
     * 
     * @OA\Post(
     *     path="/historial-clinico-backend/public/api/admin/getListaLiberados",
     *     tags={"Administrador"},
     *     operationId="getListaLiberados",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="codGrupoCia",
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
     *                 example={"codGrupoCia": "001", "fechaInicio": "01/11/2021", "fechaFin": "01/12/2021"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos Encontrados",     
     *     )
     * )
     */
    function getListaLiberados(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');


        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'fechaInicio' => 'required',
            'fechaFin' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {


            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, "BEGIN :result := PTOVENTA_CME_ADM.F_LISTA_LIBERADOS( :codGrupoCia, :fechaInicio, :fechaFin); END;");
            oci_bind_by_name($stid, ":codGrupoCia",  $codGrupoCia);
            oci_bind_by_name($stid, ":fechaInicio",  $fechaInicio);
            oci_bind_by_name($stid, ":fechaFin",  $fechaFin);
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);

            $lista = [];
            if ($stid) {

                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    foreach ($row as $key => $value) {

                        $datos = explode('Ã', $value);
                        $keys = explode("||'Ã'||", $key);

                        $abc = [
                            "key" => $datos[21] . $datos[20]. random_int(1, 999),
                            "NVL_LOGIN_USU" => $datos[0],
                            "FECH_LIBERA" => $datos[1],
                            "NVL_GLOSA" => $datos[2],
                            "FEC_CREA" => $datos[3],
                            "FEC_CREA_HORA" => $datos[4],
                            "DOCUMENTO" => $datos[5],
                            "NOMBRE" => $datos[6],
                            "EDAD" => $datos[7],
                            "ESTADO" => $datos[8],
                            "DESCRIPCION" => $datos[9],
                            "MEDICO" => $datos[10],
                            "COD_PACIENTE" => $datos[11],
                            "COD_ESTADO" => $datos[12],
                            "NVL_NRO_HC_FISICA" => $datos[13],
                            "NRO_HC_FISICA" => $datos[14],
                            "NVL_NUM_ATEN_MED" => $datos[15],
                            "IND_ANULADO" => $datos[16],
                            "COD_GRUPO_CIA" => $datos[17],
                            "COD_CIA" => $datos[18],
                            "COD_LOCAL" => $datos[19],
                            "NUM_ATEN_MED" => $datos[20],
                            "COD_PACIENTE" => $datos[21],
                            "COD_MEDICO" => $datos[22],
                            "ID_CONSULTORIO" => $datos[23],
                            "ESPECIALIDAD" => $datos[24]
                        ];

                        $lista[] = $abc;
                    }
                }
            }
            return CustomResponse::success('Tabla Lista Atenciones encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error: ' . $th->getMessage());
        }
    }

    public function obtenerVersionSistemaWeb() {
        try {
            $data = DB::select('SELECT * FROM REL_APLICACION_VERSION_WEB WHERE FLG_PERMITIDO = 1');
            if (count($data) <= 0) {
                return CustomResponse::failture('No se encontro una versión permitida del sistema.');
            }
            return CustomResponse::success('Nueva version encontrada.', $data[0]);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores');
        }
    }
}
