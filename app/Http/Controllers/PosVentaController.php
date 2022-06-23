<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosVentaController extends Controller
{
    function obtenerListaEspecialidades(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin:result := HHC_VENTAS.F_CUR_LISTA_LAB(cCodGrupoCia_in => :cCodGrupoCia_in,cCodLocal_in => :cCodLocal_in);end;');
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
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
                                'value' => $datos[1],
                                'ESPECIALIDAD' => $datos[1]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success("Lista de especialidades", $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerCajaDispoUsuario(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTIENE_CAJAS_DISP_USUARIO(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,cSecUsu_in => :cSecUsu_in);end;');
            oci_bind_by_name($stid, ':result', $result, 5);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':cSecUsu_in', $secUsu);
            oci_execute($stid);
            error_log($codLocal);
            return CustomResponse::success('Caja disponible', trim($result));
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerFechaHoraDB() {
        try {
            $data = DB::select("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy hh24:mi:ss') as fecha FROM DUAL");
            return CustomResponse::success('Fecha y hora de base de datos', trim($data[0]->fecha));
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerFechaMovCaja(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numCaja = $request->input('numCaja');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin:result := PTOVENTA_CAJ.CAJ_OBTIENE_FECHA_MOV_CAJA(cCodGrupoCia_in => :cCodGrupoCia_in,cCodLocal_in => :cCodLocal_in,nNumCajaPago_in => :nNumCajaPago_in);end;');
            oci_bind_by_name($stid, ':result', $result, 10);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':nNumCajaPago_in', $numCaja);
            oci_execute($stid);

            return CustomResponse::success('Fecha movimiento de caja', trim($result));
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function validaOperadorCaja(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');
        $tipOp = $request->input('tipOp');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu' => 'required',
            'tipOp' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_VALIDA_OPERADOR_CAJA(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,cSecUsu_in => :cSecUsu_in,cTipOp_in => :cTipOp_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':cSecUsu_in', $secUsu);
            oci_bind_by_name($stid, ':cTipOp_in', $tipOp);
            oci_execute($stid);

            return CustomResponse::success('Validada correctamente');
        } catch (\Throwable $th) {
            error_log($th);
            if (str_contains($th->getMessage(), '20013')) {
                return CustomResponse::failure('La caja del usuario ya se encuentra cerrada');
            } else if (str_contains($th->getMessage(), '20012')) {
                return CustomResponse::failure('La caja del usuario ya se encuentra aperturada');
            } else if (str_contains($th->getMessage(), '20011')) {
                return CustomResponse::failure('El usuario no posee ninguna caja activa asociada');
            }
            return CustomResponse::failure($th->getMessage());
        }
    }

    function obtenerValorCompBoleta(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $serieLocalBoleta = $request->input('serieLocalBoleta');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'serieLocalBoleta' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_F_VALOR_COMPROBANTE_BOLETA(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,cNum_SerieLocal_in => :cNum_SerieLocal_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':cNum_SerieLocal_in', $serieLocalBoleta);
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
                                'DESCRIPCION' => $datos[0],
                                'NUM_SERIE' => $datos[1],
                                'NUM_COMP' => $datos[2],
                                'TIPO_COMP' => $datos[3]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Resultados obtenidos', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerValorCompFactura(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $serieLocalFactura = $request->input('serieLocalFactura');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'serieLocalFactura' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_F_VALOR_COMP_FACTURA(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,cNum_SerieLocal_in => :cNum_SerieLocal_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':cNum_SerieLocal_in', $serieLocalBoleta);
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
                                'DESCRIPCION' => $datos[0],
                                'NUM_SERIE' => $datos[1],
                                'NUM_COMP' => $datos[2],
                                'TIPO_COMP' => $datos[3]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Resultados obtenidos', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerListaSeriesBoleta(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_LISTA_SERIES_BOLETA_CAJ(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
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
                                'NUM_SERIE' => $datos[0],
                                'NUM_SERIE_LOCAL' => $datos[1]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de series de boleta', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerListaSeriesFactura(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_LISTA_SERIES_FACTURA_CAJ(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in);end;');
            oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
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
                                'NUM_SERIE' => $datos[0],
                                'NUM_SERIE_LOCAL' => $datos[1]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de series de factura', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerMovApertura(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numCaja = $request->input('numCaja');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTENER_SEC_MOV_APERTURA(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,nNumCaj_in => :nNumCaj_in);end;');
            oci_bind_by_name($stid, ':result', $result, 10);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':nNumCaj_in', $numCaja);
            oci_execute($stid);

            return CustomResponse::success('Movimiento Apertura', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function setBloqueoCaja(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secCaja = $request->input('secCaja');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_P_FOR_UPDATE_MOV_CAJA(cCodGrupoCia_in => :cCodGrupoCia_in,cCodLocal_in => :cCodLocal_in,cSecCaja_in => :cSecCaja_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cSecCaja_in', $secCaja);
            oci_execute($stid);

            return CustomResponse::success('Caja desbloqueada');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function procesaDatosArqueo(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secCaja = $request->input('secCaja');
        $tipOp = $request->input('tipOp');
        $tipMov = $request->input('tipMov');
        $numCaja = $request->input('numCaja');
        $secUsu = $request->input('secUsu');
        $idUsu = $request->input('idUsu');
//        $ipMovCaja = $request->input('ipMovCaja');
        $ipMovCaja = $request->server->get('REMOTE_ADDR');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secCaja' => 'required',
            'tipOp' => 'required',
            'tipMov' => 'required',
            'numCaja' => 'required',
            'secUsu' => 'required',
            'idUsu' => 'required',
//            'ipMovCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_F_PROCESA_VALORES_ARQUEO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                cTipMov_in => :cTipMov_in,
                nNumCaj_in => :nNumCaj_in,
                cSecUsu_in => :cSecUsu_in,
                cIdUsu_in => :cIdUsu_in,
                cSecMovCaja_in => :cSecMovCaja_in,
                cIpMovCaja_in => :cIpMovCaja_in,
                cTipOp_in => :cTipOp_in);end;');
            oci_bind_by_name($stid, ':result', $result, 200);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':cTipMov_in', $tipMov);
            oci_bind_by_name($stid, ':nNumCaj_in', $numCaja);
            oci_bind_by_name($stid, ':cSecUsu_in', $secUsu);
            oci_bind_by_name($stid, ':cIdUsu_in', $idUsu);
            oci_bind_by_name($stid, ':cSecMovCaja_in', $secCaja);
            oci_bind_by_name($stid, ':cIpMovCaja_in', $ipMovCaja);
            oci_bind_by_name($stid, ':cTipOp_in', $tipOp);
            oci_execute($stid);

            return CustomResponse::success('La operación de cierre de caja se realizó correctamente', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function updateNumera(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codNumber = $request->input('codNumera');
        $idUsu = $request->input('idUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codNumera' => 'required',
            'idUsu' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin FARMA_UTILITY.ACTUALIZAR_NUMERA_SIN_COMMIT(
                                             cCodGrupoCia_in => :cCodGrupoCia_in,
                                             cCodLocal_in => :cCodLocal_in,
                                             cCodNumera_in => :cCodNumera_in,
                                             vIdUsuario_in => :vIdUsuario_in);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':cCodNumera_in', $codNumber);
            oci_bind_by_name($stid, ':vIdUsuario_in', $idUsu);
            oci_execute($stid);

            return CustomResponse::success('Numera actualizado');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function aceptarTransaccion() {
        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin FARMA_UTILITY.ACEPTAR_TRANSACCION; end;');
            oci_execute($stid);

            return CustomResponse::success('Transaccion aceptada');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerFechaApertura(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numCaja = $request->input('numCaja');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTENER_FECHA_APERTURA(cCodGrupoCia_in => :cCodGrupoCia_in, cCod_Local_in => :cCod_Local_in, nNumCaj_in => :nNumCaj_in);end;');
            oci_bind_by_name($stid, ':result', $result, 20);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':nNumCaj_in', $numCaja);
            oci_execute($stid);

            return CustomResponse::success('Fecha de apertura', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerTurnoActualCaja(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numCaja = $request->input('numCaja');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numCaja' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTENER_TURNO_ACTUAL_CAJA(cCodGrupoCia_in => :cCodGrupoCia_in,cCod_Local_in => :cCod_Local_in,nNumCaj_in => :nNumCaj_in);end;');
            oci_bind_by_name($stid, ':result', $result, 20);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':nNumCaj_in', $numCaja);
            oci_execute($stid);

            return CustomResponse::success('Turno actual de caja', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function setRegistraMovimientoApertura(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $numCaja = $request->input('numCaja');
        $secUsu = $request->input('secUsu');
        $codUsu = $request->input('codUsu');
        $ipMovCaja = $request->server->get('REMOTE_ADDR');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'numCaja' => 'required',
            'secUsu' => 'required',
            'codUsu' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_REGISTRA_MOVIMIENTO_APER(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                nNumCaj_in => :nNumCaj_in,
                cSecUsu_in => :cSecUsu_in,
                cCodUsu_in => :cCodUsu_in,
                cIpMovCaja => :cIpMovCaja);end;');
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
            oci_bind_by_name($stid, ':nNumCaj_in', $numCaja);
            oci_bind_by_name($stid, ':cSecUsu_in', $secUsu);
            oci_bind_by_name($stid, ':cCodUsu_in', $codUsu);
            oci_bind_by_name($stid, ':cIpMovCaja', $ipMovCaja);
            oci_execute($stid);

            return CustomResponse::success('La operación de apertura de caja se realizó correctamente');
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    // PRODUCTOS

    function obtenerListaProductos(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA_LISTA.VTA_LISTA_PROD(cCodGrupoCia_in => :cCodGrupoCia_in,cCodLocal_in => :cCodLocal_in);end;');
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
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
                                'CODIGO' => $datos[0],
                                'DESCRIPCION' => $datos[1],
                                'UNIDAD' => $datos[2],
                                'MARCA' => $datos[3],
                                'PRECIO' => $datos[5]
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success("Lista de productos y precios", $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function isValidoVerPrecioMinimo(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $secUsu = $request->input('secUsu');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'secUsu'=> 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := FARMA_UTILITY.IS_PERMITE_VER_PREC_MIN(
                vCodGrupoCia_in => :vCodGrupoCia_in,
                vCodLocal_in => :vCodLocal_in,
                vSecUsu_local_in => :vSecUsu_local_in);end;');
            oci_bind_by_name($stid, ':result', $result, 2);
            oci_bind_by_name($stid, ':vCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':vCodLocal_in', $codLocal);
            oci_bind_by_name($stid, ':vSecUsu_local_in', $secUsu);
            oci_execute($stid);

            return CustomResponse::success('Respuesta satisfactoria', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerIndSolIdUsu(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codProducto = $request->input('codProducto');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codProducto' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $result = '';
            $stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_F_GET_IND_SOL_ID_USU(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodProd_in => :cCodProd_in);end;');
            oci_bind_by_name($stid, ':result', $result, 2);
            oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
            oci_bind_by_name($stid, ':cCodProd_in', $codProducto);
            oci_execute($stid);

            return CustomResponse::success('Respuesta satisfactoria', $result);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerListaFracciones(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codProducto = $request->input('codProducto');
        $codTipoVenta = $request->input('codTipoVenta');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codProducto'=> 'required',
            'codTipoVenta'=> 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PKG_ADM_PRODUCTOS_DOS.LISTA_FRACCIONAMIENTO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodProd_in => :cCodProd_in,
                cTipoVenta_in => :cTipoVenta_in);end;');
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
            oci_bind_by_name($stid, ":cCodProd_in", $codProducto);
            oci_bind_by_name($stid, ":cTipoVenta_in", $codTipoVenta);
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
                                'DESCRIPCION' => $datos[0],
                                'PRECIO' => $datos[2],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de fracciones', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }

    function obtenerListaLoteProducto(Request $request) {
        $codGrupoCia = $request->input('codGrupoCia');
        $codLocal = $request->input('codLocal');
        $codProducto = $request->input('codProducto');

        $validator = Validator::make($request->all(), [
            'codGrupoCia' => 'required',
            'codLocal' => 'required',
            'codProducto'=> 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        }

        try {
            $conn = OracleDB::getConnection();
            $cursor = oci_new_cursor($conn);
            $stid = oci_parse($conn, 'begin :result := PKG_ADM_PRODUCTOS_DOS.GET_LISTA_LOTE_PROD(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodProd_in => :cCodProd_in);end;');
            oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
            oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
            oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
            oci_bind_by_name($stid, ":cCodProd_in", $codProducto);
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
                                'NUM_LOTE_PROD' => $datos[0],
                                'FECHA_VENC' => $datos[1],
                            ]
                        );
                    }
                }
            }
            oci_free_statement($stid);
            oci_free_statement($cursor);
            oci_close($conn);

            return CustomResponse::success('Lista de fracciones', $lista);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }
}