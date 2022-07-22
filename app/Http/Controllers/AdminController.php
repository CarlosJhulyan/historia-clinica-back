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
                return CustomResponse::failure('No se encontro una versión permitida del sistema.');
            }
            return CustomResponse::success('Nueva version encontrada.', $data[0]);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores');
        }
    }

    public function getMedicos(Request $request)
    {
        $valor = $request->input('valor');

        $validator = Validator::make($request->all(), [
            'valor' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_LISTA_MEDICO(cValor => :cValor);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cValor', $valor);
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
                                'CMP' => $datos[0],
                                'ESTADO' => $datos[1],
                                'EQUIV_ESTADO' => $datos[2],
                                'TIPO_COLEGIO' => $datos[3],
                                'COD_MEDICO' => $datos[4],
                                'NOMBRES' => $datos[5],
                                'APELLIDOS' => $datos[6],
                                'ESPECIALIDAD' => $datos[7],
                                'NUM_DOC' => $datos[8],
                                'DIRECCION' => $datos[9],
                                'USUARIO' => $datos[10],
                                'COD_SEXO' => $datos[11],
                                'SEXO' => $datos[12],
                                'FEC_NAC' => $datos[13],
                                'COD_TIPO_COLEGIO' => $datos[14],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) <= 0) return  CustomResponse::failure('No existen coincidencias');
            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function getTipoColegios()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_TIPO_COLEGIO; end;');
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

            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function getEspecialidadesMedico()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_GET_ESPECIALIDAD; end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);
            $lista = [];

            if ($stid) {
                while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    foreach ($row as $key => $value) {
                        array_push(
                            $lista,
                            [
                                'value' => $value,
                                'descripcion' => $value
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function createMedico(Request $request) {
        $cNumCMP_in = $request->input('cmp');
        $cTipoColegio = $request->input('tipoColegio');
        $cNombre_in = $request->input('nombre');
        $cApellidos_in = $request->input('apellidos');
        $cNumDoc = $request->input('numDoc');
        $cDireccion = $request->input('direccion');
        $cSexo = $request->input('sexo');
        $cFecNac = $request->input('fecNac');
        $cCodUsu = $request->input('codUsu');
        $cEspecialidad = $request->input('especialidad');

        $validator = Validator::make($request->all(), [
            'cmp' => 'required',
            'tipoColegio' => 'required',
            'nombre' => 'required',
            'apellidos' => 'required',
            'numDoc' => 'required',
            'direccion' => 'required',
            'sexo' => 'required',
            'fecNac' => 'required',
            'codUsu' => 'required',
            'especialidad' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin HHC_PTOVENTA_MEDICO.HHC_GRABA_MEDICO(
                cNumCMP_in => :cNumCMP_in,
                cTipoColegio => :cTipoColegio,
                cNombre_in => :cNombre_in,
                cApellidos_in => :cApellidos_in,
                cNumDoc => :cNumDoc,
                cDireccion => :cDireccion,
                cSexo => :cSexo,
                cFecNac => :cFecNac,
                cCodUsu => :cCodUsu,
                cEspecialidad => :cEspecialidad);end;');
            oci_bind_by_name($stid, ':cNumCMP_in', $cNumCMP_in);
            oci_bind_by_name($stid, ':cTipoColegio', $cTipoColegio);
            oci_bind_by_name($stid, ':cNombre_in', $cNombre_in);
            oci_bind_by_name($stid, ':cApellidos_in', $cApellidos_in);
            oci_bind_by_name($stid, ':cNumDoc', $cNumDoc);
            oci_bind_by_name($stid, ':cDireccion', $cDireccion);
            oci_bind_by_name($stid, ':cSexo', $cSexo);
            oci_bind_by_name($stid, ':cFecNac', $cFecNac);
            oci_bind_by_name($stid, ':cCodUsu', $cCodUsu);
            oci_bind_by_name($stid, ':cEspecialidad', $cEspecialidad);
            oci_execute($stid);
            oci_close($conn);

            return CustomResponse::success('Procedimiento completado');
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function updateStatusMedico(Request $request) {
        $cNumCMP_in = $request->input('cmp');
        $cValor = $request->input('valor');
        $cCodUsu = $request->input('codUsu');

        $validator = Validator::make($request->all(), [
            'cmp' => 'required',
            'valor' => 'required',
            'codUsu' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin HHC_PTOVENTA_MEDICO.HHC_UPDATE_MEDICO(
                cNumCMP_in => :cNumCMP_in,
                cValor => :cValor,
                cCodUsu => :cCodUsu);end;');
            oci_bind_by_name($stid, ':cNumCMP_in', $cNumCMP_in);
            oci_bind_by_name($stid, ':cValor', $cValor);
            oci_bind_by_name($stid, ':cCodUsu', $cCodUsu);
            oci_execute($stid);
            oci_close($conn);

            return CustomResponse::success('Estado de médico actualizado');
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function getConsultorioMedico()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_GET_CONSULTORIOS; end;');
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

            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function getBusMedico(Request $request)
    {

        $cId_in = $request->input('id');

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_GET_BUS(
                cConsultorio => :cId_in); end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cId_in', $cId_in);
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

            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }


    function deleteAsignacion(Request $request) {
        $cNumCMP_in = $request->input('cmp');

        $validator = Validator::make($request->all(), [
            'cmp' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin HHC_PTOVENTA_MEDICO.HHC_DELETE_ASIGNA(
                cNumCMP_in => :cNumCMP_in);end;');
            oci_bind_by_name($stid, ':cNumCMP_in', $cNumCMP_in);
            oci_execute($stid);
            oci_close($conn);

            return CustomResponse::success('Asignacion de médico borrada');
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }


    public function searchAsignaMedicos()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := HHC_PTOVENTA_MEDICO.HHC_TODOS_ASIGNA_MEDICO;end;');
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
                                'key' => $datos[0],
                                'CMP' => $datos[0],
                                'COD_MEDICO' => $datos[1],
                                'NOMBRES' => $datos[2],
                                'APELLIDOS' => $datos[3],
                                'ID_CONSULTORIO' => $datos[4],
                                'CONSULTORIO' => $datos[5],
                                'ID_BUS' => $datos[6],
                                'BUS' => $datos[7],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) <= 0) return  CustomResponse::failure('No existen coincidencias');
            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function createAsignaMedicos(Request $request) {
        $cNumCMP_in = $request->input('cmp');
        $codmedico = $request->input('codmedico');
        $idconsultorio = $request->input('idconsultorio');
        $idbus = $request->input('idbus');
        $bus = $request->input('bus');

        $validator = Validator::make($request->all(), [
            'cmp' => 'required',
            'codmedico' => 'required',
            'idconsultorio' => 'required',
            'idbus' => 'required',
            'bus' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin HHC_PTOVENTA_MEDICO.HHC_ASIGNA_MEDICO(cNumCMP_in => :cNumCMP_in,
            cCodMedico => :cCodMedico,
            idConsultorio => :idConsultorio,
            id_bus => :id_bus,
            cBus => :cBus);end;');
            oci_bind_by_name($stid, ':cNumCMP_in', $cNumCMP_in);
            oci_bind_by_name($stid, ':cCodMedico', $codmedico);
            oci_bind_by_name($stid, ':idConsultorio', $idconsultorio);
            oci_bind_by_name($stid, ':id_bus', $idbus);
            oci_bind_by_name($stid, ':cBus', $bus);
            oci_execute($stid);
            oci_close($conn);

            return CustomResponse::success('Asignacion creada satisfactoriamente');
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    public function getUsuariosActivosInactivos(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codEstado = $request->input('codEstado');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
//            'codEstado' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := PTOVENTA_ADMIN_USU.USU_LISTA_USUARIOS_LOCAL(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cEstadoActivo_in => :cEstadoActivo_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cEstadoActivo_in', $codEstado);
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
                                'SEC_USU_LOCAL' => $datos[0],
                                'APE_PAT' => $datos[1],
                                'APE_MAT' => $datos[2],
                                'NOMBRE' => $datos[3],
                                'USUARIO' => $datos[4],
                                'ESTADO' => $datos[5],
                                'DIRECCION' => $datos[6],
                                'TELEFONO' => $datos[7],
                                'FEC_NAC' => $datos[8],
                                'COD_TRAB' => $datos[11],
                                'DNI' => $datos[12],
                                'COD_TRAB_RRHH' => $datos[13],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) <= 0) return  CustomResponse::failure('No existen coincidencias');
            return CustomResponse::success('Datos encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function createUsuario(Request $request) {
        $cCodGrupoCia_in = $request->input('codGrupoCia');
        $cCodCia_in = $request->input('codCia');
        $cCodLocal_in = $request->input('codLocal');
        $cCodTrab_in = $request->input('codTrab');
        $cNomUsu_in = $request->input('nomUsu');
        $cApePat_in = $request->input('apePat');
        $cApeMat_in = $request->input('apeMat');
        $cLoginUsu_in = $request->input('loginUsu');
        $cClaveUsu_in = $request->input('claveUsu');
        $cTelefUsu_in = $request->input('telefUsu');
        $cDireccUsu_in = $request->input('direccUsu');
        $cFecNac_in = $request->input('fecNac');
        $cCodUsu_in = $request->input('codUsu');
        $cDni_in = $request->input('dni');
        $cCodTrabRRHH = $request->input('codTrabRH');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
//            'codCia' => 'required',
            'codLocal' => 'required',
//            'codTrab' => 'required',
            'nomUsu' => 'required',
            'apePat' => 'required',
            'apeMat' => 'required',
            'loginUsu' => 'required',
            'claveUsu' => 'required',
//            'telefUsu' => 'required',
//            'direccUsu' => 'required',
            'fecNac' => 'required',
            'codUsu' => 'required',
            'dni' => 'required',
//            'codTrabRH' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_ADMIN_USU.USU_INGRESA_USUARIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodCia_in => :cCodCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodTrab_in => :cCodTrab_in,
                cNomUsu_in => :cNomUsu_in,
                cApePat_in => :cApePat_in,
                cApeMat_in => :cApeMat_in,
                cLoginUsu_in => :cLoginUsu_in,
                cClaveUsu_in => :cClaveUsu_in,
                cTelefUsu_in => :cTelefUsu_in,
                cDireccUsu_in => :cDireccUsu_in,
                cFecNac_in => :cFecNac_in,
                cCodUsu_in => :cCodUsu_in,
                cDni_in => :cDni_in,
                cCodTrabRRHH => :cCodTrabRRHH);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
            oci_bind_by_name($stid, ':cCodCia_in', $cCodCia_in);
            oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
            oci_bind_by_name($stid, ':cCodTrab_in', $cCodTrab_in);
            oci_bind_by_name($stid, ':cNomUsu_in', $cNomUsu_in);
            oci_bind_by_name($stid, ':cApePat_in', $cApePat_in);
            oci_bind_by_name($stid, ':cApeMat_in', $cApeMat_in);
            oci_bind_by_name($stid, ':cLoginUsu_in', $cLoginUsu_in);
            oci_bind_by_name($stid, ':cClaveUsu_in', $cClaveUsu_in);
            oci_bind_by_name($stid, ':cTelefUsu_in', $cTelefUsu_in);
            oci_bind_by_name($stid, ':cDireccUsu_in', $cDireccUsu_in);
            oci_bind_by_name($stid, ':cFecNac_in', $cFecNac_in);
            oci_bind_by_name($stid, ':cCodUsu_in', $cCodUsu_in);
            oci_bind_by_name($stid, ':cDni_in', $cDni_in);
            oci_bind_by_name($stid, ':cCodTrabRRHH', $cCodTrabRRHH);
            oci_execute($stid);

            return CustomResponse::success('Usuario registrado correctamente');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th->getMessage(), '20014')) return CustomResponse::failure('El Login especificado ya existe');
            return CustomResponse::failure();
        }
    }

    function updateUsuario(Request $request) {
        $cCodGrupoCia_in = $request->input('codGrupoCia');
        $cCodLocal_in = $request->input('codLocal');
        $cSecUsuLocal_in = $request->input('codSecUsu');
        $cCodTrab_in = $request->input('codTrab');
        $cNomUsu_in = $request->input('nomUsu');
        $cApePat_in = $request->input('apePat');
        $cApeMat_in = $request->input('apeMat');
        $cLoginUsu_in = $request->input('loginUsu');
        $cClaveUsu_in = $request->input('claveUsu');
        $cTelefUsu_in = $request->input('telefUsu');
        $cDireccUsu_in = $request->input('direccUsu');
        $cFecNac_in = $request->input('fecNac');
        $cCodUsu_in = $request->input('codUsu');
        $cDni_in = $request->input('dni');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codSecUsu' => 'required',
            'codLocal' => 'required',
//            'codTrab' => 'required',
            'nomUsu' => 'required',
            'apePat' => 'required',
            'apeMat' => 'required',
            'loginUsu' => 'required',
            'claveUsu' => 'required',
//            'telefUsu' => 'required',
//            'direccUsu' => 'required',
            'fecNac' => 'required',
            'codUsu' => 'required',
            'dni' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_ADMIN_USU.USU_MODIFICA_USUARIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cSecUsuLocal_in => :cSecUsuLocal_in,
                cCodTrab_in => :cCodTrab_in,
                cNomUsu_in => :cNomUsu_in,
                cApePat_in => :cApePat_in,
                cApeMat_in => :cApeMat_in,
                cLoginUsu_in => :cLoginUsu_in,
                cClaveUsu_in => :cClaveUsu_in,
                cTelefUsu_in => :cTelefUsu_in,
                cDireccUsu_in => :cDireccUsu_in,
                cFecNac_in => :cFecNac_in,
                cCodUsu_in => :cCodUsu_in,
                cDni_in => :cDni_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
            oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
            oci_bind_by_name($stid, ':cSecUsuLocal_in', $cSecUsuLocal_in);
            oci_bind_by_name($stid, ':cCodTrab_in', $cCodTrab_in);
            oci_bind_by_name($stid, ':cNomUsu_in', $cNomUsu_in);
            oci_bind_by_name($stid, ':cApePat_in', $cApePat_in);
            oci_bind_by_name($stid, ':cApeMat_in', $cApeMat_in);
            oci_bind_by_name($stid, ':cLoginUsu_in', $cLoginUsu_in);
            oci_bind_by_name($stid, ':cClaveUsu_in', $cClaveUsu_in);
            oci_bind_by_name($stid, ':cTelefUsu_in', $cTelefUsu_in);
            oci_bind_by_name($stid, ':cDireccUsu_in', $cDireccUsu_in);
            oci_bind_by_name($stid, ':cFecNac_in', $cFecNac_in);
            oci_bind_by_name($stid, ':cCodUsu_in', $cCodUsu_in);
            oci_bind_by_name($stid, ':cDni_in', $cDni_in);
            oci_execute($stid);

            return CustomResponse::success('Usuario actualizado correctamente');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure();
        }
    }

    function changeEstadoUsuario(Request $request) {
        $cCodGrupoCia_in = $request->input('codGrupoCia');
        $cCodLocal_in = $request->input('codLocal');
        $cSecUsuLocal_in = $request->input('secUsu');
        $cCodUsu_in = $request->input('codUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required',
            'codUsu' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_ADMIN_USU.USU_CAMBIA_ESTADO_USU(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cSecUsuLocal_in => :cSecUsuLocal_in,
                cCodUsu_in => :cCodUsu_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
            oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
            oci_bind_by_name($stid, ':cSecUsuLocal_in', $cSecUsuLocal_in);
            oci_bind_by_name($stid, ':cCodUsu_in', $cCodUsu_in);
            oci_execute($stid);

            return CustomResponse::success('Estado de usuario actualizado correctamente');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th->getMessage(), '20015')) return CustomResponse::failure('No se puede inactivar a un usuario que este asignado a una caja.');
            return CustomResponse::failure();
        }
    }

    public function getRolesUsuario(Request $request)
    {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := PTOVENTA_ADMIN_USU.USU_LISTA_ROLES_USUARIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cSecUsuLocal_in => :cSecUsuLocal_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cSecUsuLocal_in', $secUsu);
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
                                'COD_ROL' => $datos[0],
                                'DESC_ROL' => $datos[1],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) <= 0) return  CustomResponse::failure('Sin Roles asignados');
            return CustomResponse::success('Roles encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure();
        }
    }

    public function getTodosRolesUsuario()
    {
        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);

            $stid = oci_parse($conn, 'begin :result := PTOVENTA_ADMIN_USU.USU_LISTA_ROLES;end;');
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
                                'key' => $datos[0],
                                'COD_ROL' => $datos[0],
                                'DESC_ROL' => $datos[1],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            if (count($lista) <= 0) return  CustomResponse::failure('Sin Roles asignados');
            return CustomResponse::success('Roles encontrados.', $lista);
        } catch (\Throwable $th) {
            return CustomResponse::failure();
        }
    }

    function limpiaRolesUsuario(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();

            $stid = oci_parse($conn, 'begin PTOVENTA_ADMIN_USU.USU_LIMPIA_ROLES_USUARIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cSecUsuLocal_in => :cSecUsuLocal_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cSecUsuLocal_in', $secUsu);
            oci_execute($stid);

            return CustomResponse::success('Se limpio con exito los roles de usuario');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function establecerRolUsuario(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');
        $codRol = $request->input('codRol');
        $usuCrea = $request->input('usuCrea');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required',
            'codRol' => 'required',
            'usuCrea' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();

            $stid = oci_parse($conn, 'begin PTOVENTA_ADMIN_USU.USU_AGREGA_ROL_USUARIO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cSecUsuLocal_in => :cSecUsuLocal_in,
                cCodRol_in => :cCodRol_in,
                cUsuCreaRol_in => :cUsuCreaRol_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cSecUsuLocal_in', $secUsu);
            oci_bind_by_name($stid, ':cCodRol_in', $codRol);
            oci_bind_by_name($stid, ':cUsuCreaRol_in', $usuCrea);
            oci_execute($stid);

            return CustomResponse::success('Se asigno el rol al usuario');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }
}
