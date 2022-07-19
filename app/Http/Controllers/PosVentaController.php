<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Oracle\OracleDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPJasper\PHPJasper;

class PosVentaController extends Controller
{
	function obtenerListaEspecialidades(Request $request)
	{
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

	function obtenerCajaDispoUsuario(Request $request)
	{
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
			oci_close($conn);
			return CustomResponse::success('Caja disponible', trim($result));
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerFechaHoraDB()
	{
		try {
			$data = DB::select("SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy hh24:mi:ss') as fecha FROM DUAL");
			return CustomResponse::success('Fecha y hora de base de datos', trim($data[0]->fecha));
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerFechaMovCaja(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Fecha movimiento de caja', trim($result));
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function validaOperadorCaja(Request $request)
	{
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
			oci_close($conn);

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

	function obtenerValorCompBoleta(Request $request)
	{
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

	function obtenerValorCompFactura(Request $request)
	{
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

	function obtenerListaSeriesBoleta(Request $request)
	{
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

	function obtenerListaSeriesFactura(Request $request)
	{
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

	function obtenerMovApertura(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Movimiento Apertura', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function setBloqueoCaja(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Caja desbloqueada');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function procesaDatosArqueo(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('La operación de cierre de caja se realizó correctamente', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function updateNumera(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Numera actualizado');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function aceptarTransaccion()
	{
		try {
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin FARMA_UTILITY.ACEPTAR_TRANSACCION; end;');
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Transaccion aceptada');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerFechaApertura(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Fecha de apertura', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerTurnoActualCaja(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Turno actual de caja', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function setRegistraMovimientoApertura(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('La operación de apertura de caja se realizó correctamente');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	// PRODUCTOS

	function obtenerListaProductos(Request $request)
	{
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

	function isValidoVerPrecioMinimo(Request $request)
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
			oci_close($conn);

			return CustomResponse::success('Respuesta satisfactoria', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerIndSolIdUsu(Request $request)
	{
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
			oci_close($conn);

			return CustomResponse::success('Respuesta satisfactoria', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerListaFracciones(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codProducto = $request->input('codProducto');
		$codTipoVenta = $request->input('codTipoVenta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codProducto' => 'required',
			'codTipoVenta' => 'required',
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
								'key' => $datos[0],
								'ABREVIATURA' => $datos[1],
								'PRECIO' => $datos[2],
								'PRECIO_MIN' => $datos[4],
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

	function obtenerListaLoteProducto(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codProducto = $request->input('codProducto');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codProducto' => 'required',
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

	function obtenerInfoDetalleProducto(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codProducto = $request->input('codProducto');
		$indVerifica = $request->input('indVerifica');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codProducto' => 'required',
			'indVerifica' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_OBTIENE_INFO_COMPL_PROD(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodProd_in => :cCodProd_in,
                cIndVerificaSug => :cIndVerificaSug);end;');
			oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cCodProd_in", $codProducto);
			oci_bind_by_name($stid, ":cIndVerificaSug", $indVerifica);
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
								'STOCK_FISICO' => $datos[0],
								'1' => $datos[1],
								'FECHA' => $datos[2],
								'PRECIO_VENTA' => $datos[3],
								'UNIDAD' => $datos[4],
								'GUION' => $datos[5],
								'6' => $datos[6],
								'PRECIO_LISTA' => $datos[7],
								'PRECIO_VENTA_DSCTO' => $datos[8],
								'VAL_FRAC' => $datos[9],
								'IND_ZAN' => $datos[10],
							]
						);
					}
				}
			}
			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Detalles completo de producto', $lista);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function verificaProdCamp(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codCamp = $request->input('codCamp');
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
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VERIFICA_CAMP_PROD(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodCamp_in => :cCodCamp_in,
                cCodProd_in => :cCodProd_in);end;');
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodCamp_in", $codCamp);
			oci_bind_by_name($stid, ":cCodProd_in", $codProducto);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Producto verificado', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerNuevoPrecio(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codCamp = $request->input('codCamp');
		$codProducto = $request->input('codProducto');
		$precioVenta = $request->input('precioVenta');
		$numDoc = $request->input('numDoc');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codProducto' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_FIDELIZACION.FID_F_VAR2_GET_PRECIO_PROD(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodCampana_in => :cCodCampana_in,
                cCodProducto_in => :cCodProducto_in,
                cPrecioVenta => :cPrecioVenta,
                cNumDocId_in => :cNumDocId_in);end;');
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cCodCampana_in", $codLocal);
			oci_bind_by_name($stid, ":cCodProducto_in", $codProducto);
			oci_bind_by_name($stid, ":cPrecioVenta", $codProducto);
			oci_bind_by_name($stid, ":cNumDocId_in", $codProducto);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Nuevo precio de producto', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	function obtenerPrecioRedondeado(Request $request)
	{
		$valorPrecio = $request->input('valorPrecio');

		$validator = Validator::make($request->all(), [
			'valorPrecio' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = 0.0;
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_F_NUMBER_PREC_REDONDEADO(nValPrecVta_in => :nValPrecVta_in);end;');
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_bind_by_name($stid, ":nValPrecVta_in", $valorPrecio);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Valor redondeado', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	public function getMedicosPosVenta()
	{
		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_MEDICO.LISTA_TODOS_MEDICOS; end;');
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_execute($stid);
			oci_execute($cursor);
			$lista = [];

			if ($stid) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						$datos = explode('Ã', $value);
						if (count($datos) == 7) {
							array_push(
								$lista,
								[
									'key' => $datos[0],
									'CMP' => $datos[0],
									'NOMBRE_COMPLETO' => $datos[1],
									'DESC_REFERENCIA' => $datos[2],
									'TIP_REFERENCIA' => $datos[3],
									'NOMBRE' => $datos[4],
									'APE_PAT' => $datos[5],
									'APE_MAT' => $datos[6]
								]
							);
						} else {
							array_push(
								$lista,
								[
									'key' => $datos[0],
									'CMP' => $datos[0],
									'NOMBRE_COMPLETO' => $datos[1],
									'DESC_REFERENCIA' => $datos[2],
									'TIP_REFERENCIA' => $datos[3],
									'NOMBRE' => $datos[5],
									'APE_PAT' => $datos[6],
									'APE_MAT' => $datos[7]
								]
							);
						}
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

	public function getClientesNombrePosVenta(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$palabra = $request->input('palabra');

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

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CLI.CLI_BUSCA_CLI_X_PALABRA(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cPalabra_in => :cPalabra_in);end;');
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cPalabra_in', $palabra);
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
								'key' => $datos[1],
								'COD_CLI' => $datos[1],
								'TIPO_DOC_IDENT' => $datos[0],
								'NUM_DOCUMENTO' => $datos[2],
								'CLIENTE' => $datos[3],
								'TELEFONO' => $datos[4],
								'CORREO' => $datos[5],
								'DIRECCION' => $datos[6],
								'TIP_DOCUMENTO' => $datos[7],
								'NOMBRE' => $datos[8],
								'APE_PAT' => $datos[9],
								'APE_MAT' => $datos[10],
								'TIP_DOC_IDENT' => $datos[11],
							]
						);
					}
				}
			}
			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success(count($lista) . ' registros encontrados', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	public function getClientesDocPosVenta(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$documento = $request->input('documento');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'documento' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CLI.CLI_BUSCA_CLI_X_DOC(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cDocumento_in => :cDocumento_in);end;');
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cDocumento_in', $documento);
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
								'key' => $datos[1],
								'COD_CLI' => $datos[1],
								'TIPO_DOC_IDENT' => $datos[0],
								'NUM_DOCUMENTO' => $datos[2],
								'CLIENTE' => $datos[3],
								'TELEFONO' => $datos[4],
								'CORREO' => $datos[5],
								'DIRECCION' => $datos[6],
								'TIP_DOCUMENTO' => $datos[7],
								'NOMBRE' => $datos[8],
								'APE_PAT' => $datos[9],
								'APE_MAT' => $datos[10],
								'TIP_DOC_IDENT' => $datos[11],
							]
						);
					}
				}
			}
			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success(count($lista) . ' registros encontrados', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	public function getListaReferencias()
	{
		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_MEDICO.get_lista_referencia;end;');
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
								'TIPO_REFERENCIA' => $datos[0],
								'value' => $datos[1],
								'DESCRIPCION' => $datos[1]
							]
						);
					}
				}
			}
			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success(count($lista) . ' registros encontrados', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function grabarMedico(Request $request)
	{
		$cmp = $request->input('cmp');
		$nombre = $request->input('nombre');
		$apellidoP = $request->input('apellidoP');
		$apellidoM = $request->input('apellidoM');
		$referenciaId = $request->input('referenciaId');
		$referencia = $request->input('referencia');
		$pCodVisitador = $request->input('pCodVisitador');
		$pNombreVisitador = $request->input('pNombreVisitador');

		$validator = Validator::make($request->all(), [
			'cmp' => 'required',
			'nombre' => 'required',
			'apellidoP' => 'required',
			'apellidoM' => 'required',
			'referenciaId' => 'required',
			'referencia' => 'required',
			// 'pCodVisitador' => 'required',
			// 'pNombreVisitador' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';

			$stid = oci_parse($conn, 'begin PTOVENTA_MEDICO.GRABA_MEDICO(cNumCMP_in => :cmp, cNombre_in => :nombre, cApeParte_in => :apellidoP, cApeMaterno_in => :apellidoM, cIdRef_in => :referenciaId, cDescRef_in => :referencia, cCodVisitador_in => :pCodVisitador, cNomVisitador_in => :pNombreVisitador);end;');
			oci_bind_by_name($stid, ":cmp", $cmp);
			oci_bind_by_name($stid, ":nombre", $nombre);
			oci_bind_by_name($stid, ":apellidoP", $apellidoP);
			oci_bind_by_name($stid, ":apellidoM", $apellidoM);
			oci_bind_by_name($stid, ":referenciaId", $referenciaId);
			oci_bind_by_name($stid, ":referencia", $referencia);
			oci_bind_by_name($stid, ":pCodVisitador", $pCodVisitador);
			oci_bind_by_name($stid, ":pNombreVisitador", $pNombreVisitador);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('medico registrado', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function grabarCliente(Request $request)
	{

		$pNombre = $request->input('pNombre');
		$pAPellidoPat = $request->input('pAPellidoPat');
		$pApellidoMat = $request->input('pApellidoMat');
		$pTipoDocIdent = $request->input('pTipoDocIdent');
		$pDni = $request->input('pDni');
		$pDirCliente = $request->input('pDirCliente');
		$vRazonSocial = $request->input('vRazonSocial');
		$vTelefono = $request->input('vTelefono');
		$vCorreo = $request->input('vCorreo');
		$idUsuarioLogueado = $request->input('idUsuarioLogueado');

		$validator = Validator::make($request->all(), [
			'pNombre' => 'required',
			'pAPellidoPat' => 'required',
			'pApellidoMat' => 'required',
			'pTipoDocIdent' => 'required',
			'pDni' => 'required',
			'pDirCliente' => 'required',
			//			'vRazonSocial' => 'required',
			'vTelefono' => 'required',
			//			'vCorreo' => 'required',
			'idUsuarioLogueado' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$cCodGrupoCia_in = '001';
			$cCodLocal_in = '001';
			$cCodNumera_in = '012';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CLI.CLI_AGREGA_CLI_NATURAL(cCodGrupoCia_in => :cCodGrupoCia_in, cCodLocal_in => :cCodLocal_in, cCodNumera_in => :cCodNumera_in, cNombre_in => :cNombre_in, cApellido_Pat_in => :cApellido_Pat_in, cApellido_Mat_in => :cApellido_Mat_in, cTipDocIdent_in => :cTipDocIdent_in, cNumDocIdent_in => :cNumDocIdent_in, cDirCliLocal_in => :cDirCliLocal_in, cUsuCreaCliLocal_in => :cUsuCreaCliLocal_in, cRazonSocial_in => :cRazonSocial_in, cTelefono_in => :cTelefono_in, cCorreo_in => :cCorreo_in, cFechaNac_in => :cFechaNac_in, cAcompaniante_in => :cAcompaniante_in);end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $cCodGrupoCia_in);
			oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal_in);
			oci_bind_by_name($stid, ":cCodNumera_in", $cCodNumera_in); //
			oci_bind_by_name($stid, ":cNombre_in", $pNombre);
			oci_bind_by_name($stid, ":cApellido_Pat_in", $pAPellidoPat);
			oci_bind_by_name($stid, ":cApellido_Mat_in", $pApellidoMat);
			oci_bind_by_name($stid, ":cTipDocIdent_in", $pTipoDocIdent);
			oci_bind_by_name($stid, ":cNumDocIdent_in", $pDni);
			oci_bind_by_name($stid, ":cDirCliLocal_in", $pDirCliente);
			oci_bind_by_name($stid, ":cUsuCreaCliLocal_in", $idUsuarioLogueado); //
			oci_bind_by_name($stid, ":cRazonSocial_in", $vRazonSocial);
			oci_bind_by_name($stid, ":cTelefono_in", $vTelefono);
			oci_bind_by_name($stid, ":cCorreo_in", $vCorreo);
			oci_bind_by_name($stid, ":cFechaNac_in", $cFechaNac_in);
			oci_bind_by_name($stid, ":cAcompaniante_in", $cAcompaniante_in);
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('cliente registrado', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function modificarCliente(Request $request)
	{

		$pCodCliente = $request->input('pCodCliente');
		$pNombreCliente = $request->input('pNombreCliente');
		$pApellidoPat = $request->input('pApellidoPat');
		$pApellidoMat = $request->input('pApellidoMat');
		$pDni = $request->input('pDni');
		$pDirCliente = $request->input('pDirCliente');
		$pTipoDocIdent = $request->input('pTipoDocIdent');
		$vRazonSocial = $request->input('vRazonSocial');
		$vTelefono = $request->input('vTelefono');
		$vCorreo = $request->input('vCorreo');
		$idUsuarioLogueado = $request->input('idUsuarioLogueado');

		$validator = Validator::make($request->all(), [
			'pCodCliente' => 'required',
			'pNombreCliente' => 'required',
			'pApellidoPat' => 'required',
			'pApellidoMat' => 'required',
			'pDni' => 'required',
			'pDirCliente' => 'required',
			'pTipoDocIdent' => 'required',
			'vRazonSocial' => 'required',
			'vTelefono' => 'required',
			'vCorreo' => 'required',
			'idUsuarioLogueado' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}


		try {

			$conn = OracleDB::getConnection();
			$result = '';
			$cCodGrupoCia_in = '001';
			$cCodLocal_in = '001';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CLI.CLI_ACTUALIZA_CLI_NATURAL(cCodGrupoCia_in=>:cCodGrupoCia_in,cCodLocal_in=>:cCodLocal_in,cCodCliLocal_in=>:cCodCliLocal_in,cNomCliNatural_in=>:cNomCliNatural_in,cApePatCliNatural_in=>:cApePatCliNatural_in,cAPeMatCliNatural_in=>:cAPeMatCliNatural_in,cNumDocIdent_in=>:cNumDocIdent_in,cDirCliLocal_in=>:cDirCliLocal_in,cUsuModCliLocal_in=>:cUsuModCliLocal_in,cTipoDoc_in=>:cTipoDoc_in,cRazSocial_in=>:cRazSocial_in,cTelefono_in=>:cTelefono_in,cCorreo_in=>:cCorreo_in); end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $cCodGrupoCia_in);
			oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal_in);
			oci_bind_by_name($stid, ":cCodCliLocal_in", $pCodCliente);
			oci_bind_by_name($stid, ":cNomCliNatural_in", $pNombreCliente);
			oci_bind_by_name($stid, ":cApePatCliNatural_in", $pApellidoPat);
			oci_bind_by_name($stid, ":cAPeMatCliNatural_in", $pApellidoMat);
			oci_bind_by_name($stid, ":cNumDocIdent_in", $pDni);
			oci_bind_by_name($stid, ":cDirCliLocal_in", $pDirCliente);
			oci_bind_by_name($stid, ":cUsuModCliLocal_in", $idUsuarioLogueado);
			oci_bind_by_name($stid, ":cTipoDoc_in", $pTipoDocIdent);
			oci_bind_by_name($stid, ":cRazSocial_in", $vRazonSocial);
			oci_bind_by_name($stid, ":cTelefono_in", $vTelefono);
			oci_bind_by_name($stid, ":cCorreo_in", $vCorreo);
			oci_bind_by_name($stid, ":result", $result, 1);
			oci_execute($stid);
			oci_close($conn);


			return CustomResponse::success('cliente editado', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function ObtenerIndNuevoCobro()
	{
		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_GRAL.GET_IND_NUEVO_COBRO;end;');
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfacotorio', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function obtenerNumeracion(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codNumera = $request->input('codNumera');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codNumera' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := FARMA_UTILITY.OBTENER_NUMERACION(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cCodNumera_in => :cCodNumera_in);end;');
			oci_bind_by_name($stid, ":result", $result, 20);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cCodNumera_in", $codNumera);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfacotorio', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure();
		}
	}

	// TODO: ERROR PARA TRAER LA FECHA MOD NUMERA
	function obtenerFechaModNumeraPed(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codNumera = $request->input('codNumera');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codNumera' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			// $cursor = oci_new_cursor($conn);
			$cursor = '';
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_OBTIENE_FEC_MOD_NUM_PED_W(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cCodNumera_in => :cCodNumera_in);end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cCodNumera_in", $codNumera);
			oci_bind_by_name($stid, ':result', $cursor, 20);
			oci_execute($stid);
			// oci_execute($cursor);
			$lista = [];

			// if ($stid) {
			// 	oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS);
			// 	while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
			// 		foreach ($row as $key => $value) {
			// 			$datos = explode('Ã', $value);
			// 			array_push(
			// 				$lista,
			// 				[
			// 					'key' => $datos[0],
			// 					'value' => $datos[1],
			// 					'ESPECIALIDAD' => $datos[1]
			// 				]
			// 			);
			// 		}
			// 	}
			// }
			// oci_free_statement($stid);
			// oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Fecha de modificacion de pedido', $cursor);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function inicializaNumeracionSinCommit(Request $request)
	{
		$vCodGrupoCia = $request->input('codGrupoCia');
		$vCodLocal = $request->input('codLocal');
		$pCoNumeracion = $request->input('pCoNumeracion');
		$vIdUsu = $request->input('vIdUsu');

		$validator = Validator::make($request->all(), [
			'vCodGrupoCia' => 'required',
			'vCodLocal' => 'required',
			'pCoNumeracion' => 'required',
			'vIdUsu' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin FARMA_UTILITY.INICIALIZA_NUMERA_SIN_COMMIT(
						cCodGrupoCia_in => :vCodGrupoCia_in,
						cCodLocal_in => :vCodLocal_in,
						cCodNumera_in => :pCoNumeracion_in,
						vIdUsuario_in => :vIdUsu_in);end;');
			oci_bind_by_name($stid, ":vCodGrupoCia_in", $vCodGrupoCia);
			oci_bind_by_name($stid, ":vCodLocal_in", $vCodLocal);
			oci_bind_by_name($stid, ":pCoNumeracion_in", $pCoNumeracion);
			oci_bind_by_name($stid, ":vIdUsu_in", $vIdUsu);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function grabarPedidoCabecera(Request $request)
	{

		$cCodGrupoCia_in = $request->input('cCodGrupoCia_in');
		$cCodLocal_in = $request->input('cCodLocal_in');
		$cNumPedVta_in = $request->input('cNumPedVta_in');
		$cCodCliLocal_in = $request->input('cCodCliLocal_in');
		$cSecMovCaja_in = $request->input('cSecMovCaja_in');
		$nValBrutoPedVta_in = $request->input('nValBrutoPedVta_in');
		$nValNetoPedVta_in = $request->input('nValNetoPedVta_in');
		$nValRedondeoPedVta_in = $request->input('nValRedondeoPedVta_in');
		$nValIgvPedVta_in = $request->input('nValIgvPedVta_in');
		$nValDctoPedVta_in = $request->input('nValDctoPedVta_in');
		$cTipPedVta_in = $request->input('cTipPedVta_in');
		$nValTipCambioPedVta_in = $request->input('nValTipCambioPedVta_in');
		$cNumPedDiario_in = $request->input('cNumPedDiario_in');
		$nCantItemsPedVta_in = $request->input('nCantItemsPedVta_in');
		$cEstPedVta_in = $request->input('cEstPedVta_in');
		$cTipCompPago_in = $request->input('cTipCompPago_in');
		$cNomCliPedVta_in = $request->input('cNomCliPedVta_in');
		$cDirCliPedVta_in = $request->input('cDirCliPedVta_in');
		$cRucCliPedVta_in = $request->input('cRucCliPedVta_in');
		$cUsuCreaPedVtaCab_in = $request->input('cUsuCreaPedVtaCab_in');
		$cIndDistrGratuita_in = $request->input('cIndDistrGratuita_in');
		$cIndPedidoConvenio_in = $request->input('cIndPedidoConvenio_in');
		$cCodConvenio_in = $request->input('cCodConvenio_in');
		$cCodUsuLocal_in = $request->input('cCodUsuLocal_in');
		$cIndUsoEfectivo_in = $request->input('cIndUsoEfectivo_in');
		$cIndUsoTarjeta_in = $request->input('cIndUsoTarjeta_in');
		$cCodForma_Tarjeta_in = $request->input('cCodForma_Tarjeta_in');
		$cColegioMedico_in = $request->input('cColegioMedico_in');
		$cCodCliente_in = $request->input('cCodCliente_in');
		$cIndConvBTLMF = $request->input('cIndConvBTLMF');
		$cCodSolicitud = $request->input('cCodSolicitud');
		$cNumCmp = $request->input('cNumCmp');
		$cNombreMedico = $request->input('cNombreMedico');
		$cRecetaCodCia = $request->input('cRecetaCodCia');
		$cRecetaCodLocal = $request->input('cRecetaCodLocal');
		$cRecetaNumero = $request->input('cRecetaNumero');
		$cIndSoat = $request->input('cIndSoat');
		$cDNI_PACIENTE = $request->input('cDNI_PACIENTE');
		$cNumCmp_asociado = $request->input('cNumCmp_asociado');
		$cNombreMedico_asociado = $request->input('cNombreMedico_asociado');
		$cCodPaciente = $request->input('cCodPaciente');
		$cIDRef = $request->input('cIDRef');
		$cDescRef = $request->input('cDescRef');
		$cNumCmp_visitador = $request->input('cNumCmp_visitador');
		$cNombreMedico_visitador = $request->input('cNombreMedico_visitador');
		$cIndCotizacion = $request->input('cIndCotizacion');
		$cIndReserva = $request->input('cIndReserva');
		$cCodCiaReserva = $request->input('cCodCiaReserva');
		$cCodLocalReserva = $request->input('cCodLocalReserva');
		$cCodPedidoReserva = $request->input('cCodPedidoReserva');

		$validartor = Validator::make($request->all(), [
			// 'cCodGrupoCia_in' => 'required',
			// 'cCodLocal_in' => 'required',
			// 'cNumPedVta_in' => 'required',
			// 'cCodCliLocal_in' => 'required',
			// 'cSecMovCaja_in' => 'required',
			// 'nValBrutoPedVta_in' => 'required',
			// 'nValNetoPedVta_in' => 'required',
			// 'nValRedondeoPedVta_in' => 'required',
			// 'nValIgvPedVta_in' => 'required',
			// 'nValDctoPedVta_in' => 'required',
			// 'cTipPedVta_in' => 'required',
			// 'nValTipCambioPedVta_in' => 'required',
			// 'cNumPedDiario_in' => 'required',
			// 'nCantItemsPedVta_in' => 'required',
			// 'cEstPedVta_in' => 'required',
			// 'cTipCompPago_in' => 'required',
			// 'cNomCliPedVta_in' => 'required',
			// 'cDirCliPedVta_in' => 'required',
			// 'cRucCliPedVta_in' => 'required',
			// 'cUsuCreaPedVtaCab_in' => 'required',
			// 'cIndDistrGratuita_in' => 'required',
			// 'cIndPedidoConvenio_in' => 'required',
			// 'cCodConvenio_in' => 'required',
			// 'cCodUsuLocal_in' => 'required',
			// 'cIndUsoEfectivo_in' => 'required',
			// 'cIndUsoTarjeta_in' => 'required',
			// 'cCodForma_Tarjeta_in' => 'required',
			// 'cColegioMedico_in' => 'required',
			// 'cCodCliente_in' => 'required',
			// 'cIndConvBTLMF' => 'required',
			// 'cCodSolicitud' => 'required',
			// 'cNumCmp' => 'required',
			// 'cNombreMedico' => 'required',
			// 'cRecetaCodCia' => 'required',
			// 'cRecetaCodLocal' => 'required',
			// 'cRecetaNumero' => 'required',
			// 'cIndSoat' => 'required',
			// 'cDNI_PACIENTE' => 'required',
			// 'cNumCmp_asociado' => 'required',
			// 'cNombreMedico_asociado' => 'required',
			// 'cCodPaciente' => 'required',
			// 'cIDRef' => 'required',
			// 'cDescRef' => 'required',
			// 'cNumCmp_visitador' => 'required',
			// 'cNombreMedico_visitador' => 'required',
			// 'cIndCotizacion' => 'required',
			// 'cIndReserva' => 'required',
			// 'cCodCiaReserva' => 'required',
			// 'cCodLocalReserva' => 'required',
			// 'cCodPedidoReserva' => 'required',
		]);

		if ($validartor->fails()) {
			return response()->json(['error' => $validartor->errors()], 401);
		}

		try {
			// PTOVENTA_VTA.VTA_GRABAR_PEDIDO_VTA_CAB
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin PTOVENTA_VTA.VTA_GRABAR_PEDIDO_VTA_CAB(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                cCodCliLocal_in => :cCodCliLocal_in,
                cSecMovCaja_in => :cSecMovCaja_in,
                nValBrutoPedVta_in => :nValBrutoPedVta_in,
                nValNetoPedVta_in => :nValNetoPedVta_in,
                nValRedondeoPedVta_in => :nValRedondeoPedVta_in,
                nValIgvPedVta_in => :nValIgvPedVta_in,
                nValDctoPedVta_in => :nValDctoPedVta_in,
                cTipPedVta_in => :cTipPedVta_in,
                nValTipCambioPedVta_in => :nValTipCambioPedVta_in,
                cNumPedDiario_in => :cNumPedDiario_in,
                nCantItemsPedVta_in => :nCantItemsPedVta_in,
                cEstPedVta_in => :cEstPedVta_in,
                cTipCompPago_in => :cTipCompPago_in,
                cNomCliPedVta_in => :cNomCliPedVta_in,
                cDirCliPedVta_in => :cDirCliPedVta_in,
                cRucCliPedVta_in => :cRucCliPedVta_in,
                cUsuCreaPedVtaCab_in => :cUsuCreaPedVtaCab_in,
                cIndDistrGratuita_in => :cIndDistrGratuita_in,
                cIndPedidoConvenio_in => :cIndPedidoConvenio_in,
                cCodConvenio_in => :cCodConvenio_in,
                cCodUsuLocal_in => :cCodUsuLocal_in,
                cIndUsoEfectivo_in => :cIndUsoEfectivo_in,
                cIndUsoTarjeta_in => :cIndUsoTarjeta_in,
                cCodForma_Tarjeta_in => :cCodForma_Tarjeta_in,
                cColegioMedico_in => :cColegioMedico_in,
                cCodCliente_in => :cCodCliente_in,
                cIndConvBTLMF => :cIndConvBTLMF,
                cCodSolicitud => :cCodSolicitud,
                cNumCmp => :cNumCmp,
                cNombreMedico => :cNombreMedico,
                cRecetaCodCia => :cRecetaCodCia,
                cRecetaCodLocal => :cRecetaCodLocal,
                cRecetaNumero => :cRecetaNumero,
                cIndSoat => :cIndSoat,
                cDNI_PACIENTE_in => :cDNI_PACIENTE_in,
                cNumCmp_asociado => :cNumCmp_asociado,
                cNombreMedico_asociado => :cNombreMedico_asociado,
                cCodPaciente_in => :cCodPaciente_in,
                cIDRef_in => :cIDRef_in,
                cDescRef_in => :cDescRef_in,
                cNumCmp_visitador => :cNumCmp_visitador,
                cNombreMedico_visitador => :cNombreMedico_visitador,
                cIndCotizacion => :cIndCotizacion,
                cIndReserva => :cIndReserva,
                cCodCiaReserva_in => :cCodCiaReserva_in,
                cCodLocalReserva_in => :cCodLocalReserva_in,
                cCodPedidoReserva_in => :cCodPedidoReserva_in);end;');


			oci_bind_by_name($stid, ":cCodGrupoCia_in", $cCodGrupoCia_in);
			oci_bind_by_name($stid, ":cCodLocal_in", $cCodLocal_in);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
			oci_bind_by_name($stid, ":cCodCliLocal_in", $cCodCliLocal_in);
			oci_bind_by_name($stid, ":cSecMovCaja_in", $cSecMovCaja_in);
			oci_bind_by_name($stid, ":nValBrutoPedVta_in", $nValBrutoPedVta_in);
			oci_bind_by_name($stid, ":nValNetoPedVta_in", $nValNetoPedVta_in);
			oci_bind_by_name($stid, ":nValRedondeoPedVta_in", $nValRedondeoPedVta_in);
			oci_bind_by_name($stid, ":nValIgvPedVta_in", $nValIgvPedVta_in);
			oci_bind_by_name($stid, ":nValDctoPedVta_in", $nValDctoPedVta_in);
			oci_bind_by_name($stid, ":cTipPedVta_in", $cTipPedVta_in);
			oci_bind_by_name($stid, ":nValTipCambioPedVta_in", $nValTipCambioPedVta_in);
			oci_bind_by_name($stid, ":cNumPedDiario_in", $cNumPedDiario_in);
			oci_bind_by_name($stid, ":nCantItemsPedVta_in", $nCantItemsPedVta_in);
			oci_bind_by_name($stid, ":cEstPedVta_in", $cEstPedVta_in);
			oci_bind_by_name($stid, ":cTipCompPago_in", $cTipCompPago_in);
			oci_bind_by_name($stid, ":cNomCliPedVta_in", $cNomCliPedVta_in);
			oci_bind_by_name($stid, ":cDirCliPedVta_in", $cDirCliPedVta_in);
			oci_bind_by_name($stid, ":cRucCliPedVta_in", $cRucCliPedVta_in);
			oci_bind_by_name($stid, ":cUsuCreaPedVtaCab_in", $cUsuCreaPedVtaCab_in);
			oci_bind_by_name($stid, ":cIndDistrGratuita_in", $cIndDistrGratuita_in);
			oci_bind_by_name($stid, ":cIndPedidoConvenio_in", $cIndPedidoConvenio_in);
			oci_bind_by_name($stid, ":cCodConvenio_in", $cCodConvenio_in);
			oci_bind_by_name($stid, ":cCodUsuLocal_in", $cCodUsuLocal_in);
			oci_bind_by_name($stid, ":cIndUsoEfectivo_in", $cIndUsoEfectivo_in);
			oci_bind_by_name($stid, ":cIndUsoTarjeta_in", $cIndUsoTarjeta_in);
			oci_bind_by_name($stid, ":cCodForma_Tarjeta_in", $cCodForma_Tarjeta_in);
			oci_bind_by_name($stid, ":cColegioMedico_in", $cColegioMedico_in);
			oci_bind_by_name($stid, ":cCodCliente_in", $cCodCliente_in);
			oci_bind_by_name($stid, ":cIndConvBTLMF", $cIndConvBTLMF);
			oci_bind_by_name($stid, ":cCodSolicitud", $cCodSolicitud);
			oci_bind_by_name($stid, ":cNumCmp", $cNumCmp);
			oci_bind_by_name($stid, ":cNombreMedico", $cNombreMedico);
			oci_bind_by_name($stid, ":cRecetaCodCia", $cRecetaCodCia);
			oci_bind_by_name($stid, ":cRecetaCodLocal", $cRecetaCodLocal);
			oci_bind_by_name($stid, ":cRecetaNumero", $cRecetaNumero);
			oci_bind_by_name($stid, ":cIndSoat", $cIndSoat);
			oci_bind_by_name($stid, ":cDNI_PACIENTE_in", $cDNI_PACIENTE);
			oci_bind_by_name($stid, ":cNumCmp_asociado", $cNumCmp_asociado);
			oci_bind_by_name($stid, ":cNombreMedico_asociado", $cNombreMedico_asociado);
			oci_bind_by_name($stid, ":cCodPaciente_in", $cCodPaciente);
			oci_bind_by_name($stid, ":cIDRef_in", $cIDRef);
			oci_bind_by_name($stid, ":cDescRef_in", $cDescRef);
			oci_bind_by_name($stid, ":cNumCmp_visitador", $cNumCmp_visitador);
			oci_bind_by_name($stid, ":cNombreMedico_visitador", $cNombreMedico_visitador);
			oci_bind_by_name($stid, ":cIndCotizacion", $cIndCotizacion);
			oci_bind_by_name($stid, ":cIndReserva", $cIndReserva);
			oci_bind_by_name($stid, ":cCodCiaReserva_in", $cCodCiaReserva);
			oci_bind_by_name($stid, ":cCodLocalReserva_in", $cCodLocalReserva);
			oci_bind_by_name($stid, ":cCodPedidoReserva_in", $cCodPedidoReserva);
			oci_execute($stid);
			$result = '';
			oci_close($conn);

			return CustomResponse::success('Cabecera registrada', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function grabarPedidoDetalle(Request $request)
	{
		$cCodGrupoCia_in = $request->input('cCodGrupoCia_in');
		$cCodLocal_in = $request->input('cCodLocal_in');
		$cNumPedVta_in = $request->input('cNumPedVta_in');
		$nSecPedVtaDet_in = $request->input('nSecPedVtaDet_in');
		$cCodProd_in = $request->input('cCodProd_in');
		$nCantAtendida_in = $request->input('nCantAtendida_in');
		$nValPrecVta_in = $request->input('nValPrecVta_in');
		$nValPrecTotal_in = $request->input('nValPrecTotal_in');
		$nPorcDcto1_in = $request->input('nPorcDcto1_in');
		$nPorcDcto2_in = $request->input('nPorcDcto2_in');
		$nPorcDcto3_in = $request->input('nPorcDcto3_in');
		$nPorcDctoTotal_in = $request->input('nPorcDctoTotal_in');
		$cEstPedVtaDet_in = $request->input('cEstPedVtaDet_in');
		$nValTotalBono_in = $request->input('nValTotalBono_in');
		$nValFrac_in = $request->input('nValFrac_in');
		$nSecCompPago_in = $request->input('nSecCompPago_in');
		$cSecUsuLocal_in = $request->input('cSecUsuLocal_in');
		$nValPrecLista_in = $request->input('nValPrecLista_in');
		$nValIgv_in = $request->input('nValIgv_in');
		$cUnidVta_in = $request->input('cUnidVta_in');
		$cNumTelRecarga_in = $request->input('cNumTelRecarga_in');
		$cUsuCreaPedVtaDet_in = $request->input('cUsuCreaPedVtaDet_in');
		$nValPrecPub = $request->input('nValPrecPub');
		$cCodProm_in = $request->input('cCodProm_in');
		$cIndOrigen_in = $request->input('cIndOrigen_in');
		$nCantxDia_in = $request->input('nCantxDia_in');
		$nCantDias_in = $request->input('nCantDias_in');
		$nAhorroPack = $request->input('nAhorroPack');
		$cSecResp_in = $request->input('cSecResp_in');
		$vNumLoteProd_in = $request->input('vNumLoteProd_in');

		$validartor = Validator::make($request->all(), [
			'cCodGrupoCia_in' => 'required',
			'cCodLocal_in' => 'required',
			'cNumPedVta_in' => 'required',
			'nSecPedVtaDet_in' => 'required',
			'cCodProd_in' => 'required',
			'nCantAtendida_in' => 'required',
			'nValPrecVta_in' => 'required',
			'nValPrecTotal_in' => 'required',
			'nPorcDcto1_in' => 'required',
			'nPorcDcto2_in' => 'required',
			'nPorcDcto3_in' => 'required',
			'nPorcDctoTotal_in' => 'required',
			'cEstPedVtaDet_in' => 'required',
			'nValTotalBono_in' => 'required',
			'nValFrac_in' => 'required',
			// 'nSecCompPago_in' => 'required',
			'cSecUsuLocal_in' => 'required',
			'nValPrecLista_in' => 'required',
			'nValIgv_in' => 'required',
			'cUnidVta_in' => 'required',
			// 'cNumTelRecarga_in' => 'required',
			'cUsuCreaPedVtaDet_in' => 'required',
			'nValPrecPub' => 'required',
			// 'cCodProm_in' => 'required',
			'cIndOrigen_in' => 'required',
			// 'nCantxDia_in' => 'required',
			// 'nCantDias_in' => 'required',
			// 'nAhorroPack' => 'required',
			'cSecResp_in' => 'required',
			'vNumLoteProd_in' => 'required',
		]);

		if ($validartor->fails()) {
			return CustomResponse::failure($validartor->errors()->all());
		}


		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin PTOVTA_RESPALDO_STK.PVTA_P_GRAB_PED_VTA_DET(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                nSecPedVtaDet_in => :nSecPedVtaDet_in,
                cCodProd_in => :cCodProd_in,
                nCantAtendida_in => :nCantAtendida_in,
                nValPrecVta_in => :nValPrecVta_in,
                nValPrecTotal_in => :nValPrecTotal_in,
                nPorcDcto1_in => :nPorcDcto1_in,
                nPorcDcto2_in => :nPorcDcto2_in,
                nPorcDcto3_in => :nPorcDcto3_in,
                nPorcDctoTotal_in => :nPorcDctoTotal_in,
                cEstPedVtaDet_in => :cEstPedVtaDet_in,
                nValTotalBono_in => :nValTotalBono_in,
                nValFrac_in => :nValFrac_in,
                nSecCompPago_in => :nSecCompPago_in,
                cSecUsuLocal_in => :cSecUsuLocal_in,
                nValPrecLista_in => :nValPrecLista_in,
                nValIgv_in => :nValIgv_in,
                cUnidVta_in => :cUnidVta_in,
                cNumTelRecarga_in => :cNumTelRecarga_in,
                cUsuCreaPedVtaDet_in => :cUsuCreaPedVtaDet_in,
                nValPrecPub => :nValPrecPub,
                cCodProm_in => :cCodProm_in,
                cIndOrigen_in => :cIndOrigen_in,
                nCantxDia_in => :nCantxDia_in,
                nCantDias_in => :nCantDias_in,
                nAhorroPack => :nAhorroPack,
                cSecResp_in => :cSecResp_in,
                vNumLoteProd_in => :vNumLoteProd_in);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_bind_by_name($stid, ':nSecPedVtaDet_in', $nSecPedVtaDet_in);
			oci_bind_by_name($stid, ':cCodProd_in', $cCodProd_in);
			oci_bind_by_name($stid, ':nCantAtendida_in', $nCantAtendida_in);
			oci_bind_by_name($stid, ':nValPrecVta_in', $nValPrecVta_in);
			oci_bind_by_name($stid, ':nValPrecTotal_in', $nValPrecTotal_in);
			oci_bind_by_name($stid, ':nPorcDcto1_in', $nPorcDcto1_in);
			oci_bind_by_name($stid, ':nPorcDcto2_in', $nPorcDcto2_in);
			oci_bind_by_name($stid, ':nPorcDcto3_in', $nPorcDcto3_in);
			oci_bind_by_name($stid, ':nPorcDctoTotal_in', $nPorcDctoTotal_in);
			oci_bind_by_name($stid, ':cEstPedVtaDet_in', $cEstPedVtaDet_in);
			oci_bind_by_name($stid, ':nValTotalBono_in', $nValTotalBono_in);
			oci_bind_by_name($stid, ':nValFrac_in', $nValFrac_in);
			oci_bind_by_name($stid, ':nSecCompPago_in', $nSecCompPago_in);
			oci_bind_by_name($stid, ':cSecUsuLocal_in', $cSecUsuLocal_in);
			oci_bind_by_name($stid, ':nValPrecLista_in', $nValPrecLista_in);
			oci_bind_by_name($stid, ':nValIgv_in', $nValIgv_in);
			oci_bind_by_name($stid, ':cUnidVta_in', $cUnidVta_in);
			oci_bind_by_name($stid, ':cNumTelRecarga_in', $cNumTelRecarga_in);
			oci_bind_by_name($stid, ':cUsuCreaPedVtaDet_in', $cUsuCreaPedVtaDet_in);
			oci_bind_by_name($stid, ':nValPrecPub', $nValPrecPub);
			oci_bind_by_name($stid, ':cCodProm_in', $cCodProm_in);
			oci_bind_by_name($stid, ':cIndOrigen_in', $cIndOrigen_in);
			oci_bind_by_name($stid, ':nCantxDia_in', $nCantxDia_in);
			oci_bind_by_name($stid, ':nCantDias_in', $nCantDias_in);
			oci_bind_by_name($stid, ':nAhorroPack', $nAhorroPack);
			oci_bind_by_name($stid, ':cSecResp_in', $cSecResp_in);
			oci_bind_by_name($stid, ':vNumLoteProd_in', $vNumLoteProd_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Detalle de pedido guardado', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function obtieneFormasPagoSinConvenio(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$indConvenio = $request->input('indConvenio');
		$codConvenio = $request->input('codConvenio');
		$codCliente = $request->input('codCliente');
		$numPed = $request->input('numPed');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			// 'indConvenio' => 'required',
			// 'codConvenio' => 'required',
			// 'codCliente' => 'required',
			'numPed' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTIENE_FORMAS_PAG_SINCONV(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cIndPedConvenio => :cIndPedConvenio,
						cCodConvenio => :cCodConvenio,
						cCodCli_in => :cCodCli_in,
						cNumPed_in => :cNumPed_in);end;');
			oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cIndPedConvenio", $indConvenio);
			oci_bind_by_name($stid, ":cCodConvenio", $codConvenio);
			oci_bind_by_name($stid, ":cCodCli_in", $codCliente);
			oci_bind_by_name($stid, ":cNumPed_in", $numPed);
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
								'COD_FORMA_PAGO' => $datos[0],
								'value' => $datos[1],
								'DESC_CORTA_FORMA_PAGO' => $datos[1]
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio', $lista);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function setNuSecNumeracionNoCommit(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$pCoNumeracion = $request->input('pCoNumeracion');
		$vIdUsu = $request->input('vIdUsu');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'pCoNumeracion' => 'required',
			'vIdUsu' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			//Correccion Procedure
			//$stid = oci_parse($conn, 'begin :result := FARMA_UTILITY.ACTUALIZAR_NUMERA_SIN_COMMIT(
			$stid = oci_parse($conn, 'begin FARMA_UTILITY.ACTUALIZAR_NUMERA_SIN_COMMIT(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cCodNumera_in => :cCodNumera_in,
						vIdUsuario_in => :vIdUsuario_in);end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cCodNumera_in", $pCoNumeracion);
			oci_bind_by_name($stid, ":vIdUsuario_in", $vIdUsu);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function validarValorVentaNeto(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$is_cotizacion = $request->input('is_cotizacion');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'is_cotizacion' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			if (filter_var($is_cotizacion, FILTER_VALIDATE_BOOLEAN)) { // Arreglar Booleano
				//Mejora Cobro
				//$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_P_VALIDAR_VALOR_COTI(
				$stid = oci_parse($conn, 'begin PTOVENTA_VTA.VTA_P_VALIDAR_VALOR_COTI(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cNumPedVta_in => :cNumPedVta_in);end;');
				oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
				oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
				oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
				oci_execute($stid);
			} else {
				//Mejora Cobro
				//$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_P_VALIDAR_VALOR_VTA(
				$stid = oci_parse($conn, 'begin PTOVENTA_VTA.VTA_P_VALIDAR_VALOR_VTA(
							cCodGrupoCia_in => :cCodGrupoCia_in,
							cCodLocal_in => :cCodLocal_in,
							cNumPedVta_in => :cNumPedVta_in);end;');
				oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
				oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
				oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
				oci_execute($stid);
			}
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function procesaPedidoEspecialidad(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			//Mejora Cobro
			//$stid = oci_parse($conn, 'begin :result := HHC_CAJA_HHSUR.P_PROCESA_PEDIDO(
			$stid = oci_parse($conn, 'begin HHC_CAJA_HHSUR.P_PROCESA_PEDIDO(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cargaListaCajaEspecialidad(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := HHC_CAJA_HHSUR.F_CUR_LISTA_ESP_PEDIDO(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
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
								'NOM_LAB' => $datos[0],
								'EST_PED_VTA' => $datos[1],
								'VAL_NETO_PED_VTA' => $datos[2],
								'NEW_NUM_PED_VTA' => $datos[3],
								'num_ped_diario' => $datos[4],
								'fec_ped_vta' => $datos[5]
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio', $lista); //devolver lista
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cargaListaCajaDetEspecialidad(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := HHC_CAJA_HHSUR.F_CUR_LISTA_DET_ESP_PEDIDO(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCodLocal_in => :cCodLocal_in,
						cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
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
								'cod_prod' => $datos[0],
								'desc_prod' => $datos[1],
								'val_prec_vta' => $datos[2],
								'cant_atendida' => $datos[3],
								'val_prec_total' => $datos[4],
								'cod_lab' => $datos[5],
								'nom_lab' => $datos[6]
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio', $lista); //devolver lista
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function getPermiteCobrarPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVTA_RESPALDO_STK.F_EXISTE_STOCK_PEDIDO(
							cCodGrupoCia_in => :cCodGrupoCia_in,
							cCodLocal_in => :cCodLocal_in,
							cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ":result", $result, 1);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function grabaInicioFinProcesoCobroPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');
		$cTmpTipo_in = $request->input('cTmpTipo_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
			'cTmpTipo_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin :result := FARMA_GRAL.CAJ_REGISTRA_TMP_INI_FIN_COBRO(
						cCodGrupoCia_in => :cCodGrupoCia_in,
						cCod_Local_in => :cCod_Local_in,
						cNum_Ped_Vta_in => :cNum_Ped_Vta_in,
						cTmpTipo_in => :cTmpTipo_in);end;');
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCod_Local_in", $codLocal);
			oci_bind_by_name($stid, ":cNum_Ped_Vta_in", $cNumPedVta_in);
			oci_bind_by_name($stid, ":cTmpTipo_in", $cTmpTipo_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cobraPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');
		$cSecMovCaja_in = $request->input('cSecMovCaja_in');
		$cCodNumera_in = $request->input('cCodNumera_in');
		$cTipCompPago_in = $request->input('cTipCompPago_in');
		$cCodMotKardex_in = $request->input('cCodMotKardex_in');
		$cTipDocKardex_in = $request->input('cTipDocKardex_in');
		$cCodNumeraKardex_in = $request->input('cCodNumeraKardex_in');
		$cUsuCreaCompPago_in = $request->input('cUsuCreaCompPago_in');
		$cDescDetalleForPago_in = $request->input('cDescDetalleForPago_in');
		$cPermiteCampana = $request->input('cPermiteCampana');
		$cDni_in = $request->input('cDni_in');
		$cNumCompPagoImpr_in = $request->input('cNumCompPagoImpr_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
			'cSecMovCaja_in' => 'required',
			'cCodNumera_in' => 'required',
			'cTipCompPago_in' => 'required',
			'cCodMotKardex_in' => 'required',
			'cTipDocKardex_in' => 'required',
			'cCodNumeraKardex_in' => 'required',
			'cUsuCreaCompPago_in' => 'required',
			'cDescDetalleForPago_in' => 'required',
			'cPermiteCampana' => 'required',
			'cDni_in' => 'required',
			'cNumCompPagoImpr_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVTA_RESPALDO_STK.F_EXISTE_STOCK_PEDIDO(
				cCodGrupoCia_in => :cCodGrupoCia_in,
				cCodLocal_in => :cCodLocal_in,
				cNumPedVta_in => :cNumPedVta_in,
				cSecMovCaja_in => :cSecMovCaja_in,
				cCodNumera_in => :cCodNumera_in,
				cTipCompPago_in => :cTipCompPago_in,
				cCodMotKardex_in => :cCodMotKardex_in,
				cTipDocKardex_in => :cTipDocKardex_in,
				cCodNumeraKardex_in => :cCodNumeraKardex_in,
				cUsuCreaCompPago_in => :cUsuCreaCompPago_in,
				cDescDetalleForPago_in => :cDescDetalleForPago_in,
				cPermiteCampana => :cPermiteCampana,
				cDni_in => :cDni_in,
				cNumCompPagoImpr_in => :cNumCompPagoImpr_in);end;');
			oci_bind_by_name($stid, ":result", $result, 7);
			oci_bind_by_name($stid, ":cCodGrupoCia_in", $codGrupoCia);
			oci_bind_by_name($stid, ":cCodLocal_in", $codLocal);
			oci_bind_by_name($stid, ":cNumPedVta_in", $cNumPedVta_in);
			oci_bind_by_name($stid, ":cSecMovCaja_in", $cSecMovCaja_in);
			oci_bind_by_name($stid, ":cCodNumera_in", $cCodNumera_in);
			oci_bind_by_name($stid, ":cTipCompPago_in", $cTipCompPago_in);
			oci_bind_by_name($stid, ":cCodMotKardex_in", $cCodMotKardex_in);
			oci_bind_by_name($stid, ":cTipDocKardex_in", $cTipDocKardex_in);
			oci_bind_by_name($stid, ":cCodNumeraKardex_in", $cCodNumeraKardex_in);
			oci_bind_by_name($stid, ":cUsuCreaCompPago_in", $cUsuCreaCompPago_in);
			oci_bind_by_name($stid, ":cDescDetalleForPago_in", $cDescDetalleForPago_in);
			oci_bind_by_name($stid, ":cPermiteCampana", $cPermiteCampana);
			oci_bind_by_name($stid, ":cDni_in", $cDni_in);
			oci_bind_by_name($stid, ":cNumCompPagoImpr_in", $cNumCompPagoImpr_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento satisfactorio', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	// Jhulyan

	function validaStockPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numPedido = $request->input('numPedido');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedido' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVTA_RESPALDO_STK.F_EXISTE_STOCK_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ':result', $result, 50);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cNumPedVta_in', $numPedido);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Proceso de validación correcto', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function grabaInicioFinCobro(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numPedido = $request->input('numPedido');
		$tipoTmp = $request->input('tipoTmp');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedido' => 'required',
			'tipoTmp' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin FARMA_GRAL.CAJ_REGISTRA_TMP_INI_FIN_COBRO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCod_Local_in => :cCod_Local_in,
                cNum_ped_Vta_in => :cNum_ped_Vta_in,
                cTmpTipo_in => :cTmpTipo_in);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCod_Local_in', $codLocal);
			oci_bind_by_name($stid, ':cNum_ped_Vta_in', $numPedido);
			oci_bind_by_name($stid, ':cTmpTipo_in', $tipoTmp);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Grabado inicio correcto');
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function validaSiFacturaElectronica(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := SVB_FE_COBRO.IS_FACT_ELECTRONICO_LOCAL(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in);end;');
			oci_bind_by_name($stid, ':result', $result, 20);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Proceso de validación de factura correcto', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function verificaEstadoPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numPedido = $request->input('numPedido');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedido' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTIENE_ESTADO_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ':result', $result, 50);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cNumPedVta_in', $numPedido);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Estado de pedido verificado correctamente', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cajCobraPedido(Request $request)
	{

		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numPedido = $request->input('numPedido');
		$secMovCaja = $request->input('secMovCaja');
		$codNumera = $request->input('codNumera');
		$tipCompPago = $request->input('tipCompPago');
		$codMotKardex = $request->input('codMotKardex');
		$tipDocKardex = $request->input('tipDocKardex');
		$codNumeraKardex = $request->input('codNumeraKardex');
		$usuCreaCompPago = $request->input('usuCreaCompPago');
		$descDetalleForPago = $request->input('descDetalleForPago');
		$permiteCampana = $request->input('permiteCampana');
		$dni = $request->input('dni');
		$numCompPagoImpr = $request->input('numCompPagoImpr');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedido' => 'required',
			'secMovCaja' => 'required',
			'codNumera' => 'required',
			'tipCompPago' => 'required',
			'codMotKardex' => 'required',
			'tipDocKardex' => 'required',
			'codNumeraKardex' => 'required',
			'usuCreaCompPago' => 'required',
			// 'descDetalleForPago' => 'required',
			'permiteCampana' => 'required',
			'dni' => 'required',
			// 'numCompPagoImpr' => 'required'
		]);


		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$result = '';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_COBRA_PEDIDO(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in,
								cSecMovCaja_in => :cSecMovCaja_in,
								cCodNumera_in => :cCodNumera_in,
								cTipCompPago_in => :cTipCompPago_in,
								cCodMotKardex_in => :cCodMotKardex_in,
								cTipDocKardex_in => :cTipDocKardex_in,
								cCodNumeraKardex_in => :cCodNumeraKardex_in,
								cUsuCreaCompPago_in => :cUsuCreaCompPago_in,
								cDescDetalleForPago_in => :cDescDetalleForPago_in,
								cPermiteCampana => :cPermiteCampana,
								cDni_in => :cDni_in,
								cNumCompPagoImpr_in => :cNumCompPagoImpr_in);end;');


			oci_bind_by_name($stid, ':result', $result, 20);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cNumPedVta_in', $numPedido);
			oci_bind_by_name($stid, ':cSecMovCaja_in', $secMovCaja);
			oci_bind_by_name($stid, ':cCodNumera_in', $codNumera);
			oci_bind_by_name($stid, ':cTipCompPago_in', $tipCompPago);
			oci_bind_by_name($stid, ':cCodMotKardex_in', $codMotKardex);
			oci_bind_by_name($stid, ':cTipDocKardex_in', $tipDocKardex);
			oci_bind_by_name($stid, ':cCodNumeraKardex_in', $codNumeraKardex);
			oci_bind_by_name($stid, ':cUsuCreaCompPago_in', $usuCreaCompPago);
			oci_bind_by_name($stid, ':cDescDetalleForPago_in', $descDetalleForPago);
			oci_bind_by_name($stid, ':cPermiteCampana', $permiteCampana);
			oci_bind_by_name($stid, ':cDni_in', $dni);
			oci_bind_by_name($stid, ':cNumCompPagoImpr_in', $numCompPagoImpr);

			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Cobrar Pedido Exitoso', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure(
				$th->getMessage()
			);
		}
	}

	function getSecuenciaMovCaja(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numCajaPago = $request->input('numCajaPago');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numCajaPago' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$result = '';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTIENE_SEC_MOV_CAJA(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								nNumCajaPago_in => :nNumCajaPago_in);end;');

			oci_bind_by_name($stid, ':result', $result, 50);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':nNumCajaPago_in', $numCajaPago);

			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Obtener Secuencia Mov Caja Exitoso', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function cajGrabNewFormPagoPedido(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$codFormaPago = $request->input('codFormaPago');
		$numPedido = $request->input('numPedido');
		$imPago = $request->input('imPago');
		$tipMoneda = $request->input('tipMoneda');
		$valTipCambio = $request->input('valTipCambio');
		$valVuelto = $request->input('valVuelto');
		$imTotalPago = $request->input('imTotalPago');
		$numTarj = $request->input('numTarj');
		$fecVencTarj = $request->input('fecVencTarj');
		$nomTarj = $request->input('nomTarj');
		$canCupon = $request->input('canCupon');
		$usuCreaFormaPagoPed = $request->input('usuCreaFormaPagoPed');
		$dni = $request->input('dni');
		$codAtori = $request->input('codAtori');
		$lote = $request->input('lote');
		$numOperacion = $request->input('numOperacion');
		$secFormaPago = $request->input('secFormaPago');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codFormaPago' => 'required',
			'numPedido' => 'required',
			'imPago' => 'required',
			'tipMoneda' => 'required',
			'valTipCambio' => 'required',
			'valVuelto' => 'required',
			'imTotalPago' => 'required',
			//			'numTarj' => 'required',
			//			'fecVencTarj' => 'required',
			//			'nomTarj' => 'required',
			'canCupon' => 'required',
			'usuCreaFormaPagoPed' => 'required',
			// 'dni' => 'required',
			//			'codAtori' => 'required',
			'lote' => 'required',
			//			'numOperacion' => 'required',
			'secFormaPago' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_GRAB_NEW_FORM_PAGO_PEDIDO(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cCodFormaPago_in => :cCodFormaPago_in,
								cNumPedVta_in => :cNumPedVta_in,
								nImPago_in => :nImPago_in,
								cTipMoneda_in => :cTipMoneda_in,
								nValTipCambio_in => :nValTipCambio_in,
								nValVuelto_in => :nValVuelto_in,
								nImTotalPago_in => :nImTotalPago_in,
								cNumTarj_in => :cNumTarj_in,
								cFecVencTarj_in => :cFecVencTarj_in,
								cNomTarj_in => :cNomTarj_in,
								cCanCupon_in => :cCanCupon_in,
								cUsuCreaFormaPagoPed_in => :cUsuCreaFormaPagoPed_in,
								cDNI_in => :cDNI_in,
								cCodAtori_in => :cCodAtori_in,
								cLote_in => :cLote_in,
								cNumOperacion_in => :cNumOperacion_in,
								cSecFormaPago_in => :cSecFormaPago_in
								); end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cCodFormaPago_in', $codFormaPago);
			oci_bind_by_name($stid, ':cNumPedVta_in', $numPedido);
			oci_bind_by_name($stid, ':nImPago_in', $imPago);

			oci_bind_by_name($stid, ':cTipMoneda_in', $tipMoneda);
			oci_bind_by_name($stid, ':nValTipCambio_in', $valTipCambio);
			oci_bind_by_name($stid, ':nValVuelto_in', $valVuelto);
			oci_bind_by_name($stid, ':nImTotalPago_in', $imTotalPago);
			oci_bind_by_name($stid, ':cNumTarj_in', $numTarj);
			oci_bind_by_name($stid, ':cFecVencTarj_in', $fecVencTarj);
			oci_bind_by_name($stid, ':cNomTarj_in', $nomTarj);
			oci_bind_by_name($stid, ':cCanCupon_in', $canCupon);
			oci_bind_by_name($stid, ':cUsuCreaFormaPagoPed_in', $usuCreaFormaPagoPed);
			oci_bind_by_name($stid, ':cDNI_in', $dni);
			oci_bind_by_name($stid, ':cCodAtori_in', $codAtori);
			oci_bind_by_name($stid, ':cLote_in', $lote);
			oci_bind_by_name($stid, ':cNumOperacion_in', $numOperacion);
			oci_bind_by_name($stid, ':cSecFormaPago_in', $secFormaPago);

			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Forma de pago grabada');
		} catch (\Exception $e) {
			return CustomResponse::failure($e->getMessage());
		}
	}


	function cajFVerificaPedForPag(Request $request)
	{
		$codGrupoCia = $request->input('codGrupoCia');
		$codLocal = $request->input('codLocal');
		$numPedido = $request->input('cNumPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$result = '';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_F_VERIFICA_PED_FOR_PAG(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in
								); end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $codGrupoCia);
			oci_bind_by_name($stid, ':cCodLocal_in', $codLocal);
			oci_bind_by_name($stid, ':cNumPedVta_in', $numPedido);
			oci_bind_by_name($stid, ':result', $result, 100, SQLT_CHR);

			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('CAJA VERIFICADA', $result);
		} catch (\Exception $e) {
			error_log($e);
			return CustomResponse::failure($e->getMessage());
		}
	}

	function anulaPedidoPendiente(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta');
		$vIdUsu_in = $request->input('vIdUsu_in');
		$cModulo_in = $request->input('cModulo_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta' => 'required',
			'vIdUsu_in' => 'required',
			'cModulo_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin PTOVENTA_CAJ_ANUL.CAJ_ANULAR_PEDIDO_PENDIENTE(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                vIdUsu_in => :vIdUsu_in,
                cModulo_in => :cModulo_in);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_bind_by_name($stid, ':vIdUsu_in', $vIdUsu_in);
			oci_bind_by_name($stid, ':cModulo_in', $cModulo_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Pedido pendiente anulado', null);
		} catch (\Exception $e) {
			return CustomResponse::failure($e->getMessage());
		}
	}

	function actualizaCliPedido(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');
		$cCodCliLocal_in = $request->input('cCodCliLocal_in');
		$cNomCliPed_in = $request->input('cNomCliPed_in');
		$cDirCliLocal_in = $request->input('cDirCliLocal_in');
		$cRucCliPed_in = $request->input('cRucCliPed_in');
		$cUsuModPedVtaCab_in = $request->input('cUsuModPedVtaCab_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
			'cCodCliLocal_in' => 'required',
			'cNomCliPed_in' => 'required',
			'cDirCliLocal_in' => 'required',
			'cRucCliPed_in' => 'required',
			'cUsuModPedVtaCab_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_ACTUALIZA_CLI_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in,
                cCodCliLocal_in => :cCodCliLocal_in,
                cNomCliPed_in => :cNomCliPed_in,
                cDirCliLocal_in => :cDirCliLocal_in,
                cRucCliPed_in => :cRucCliPed_in,
                cUsuModPedVtaCab_in => :cUsuModPedVtaCab_in);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_bind_by_name($stid, ':cCodCliLocal_in', $cCodCliLocal_in);
			oci_bind_by_name($stid, ':cNomCliPed_in', $cNomCliPed_in);
			oci_bind_by_name($stid, ':cDirCliLocal_in', $cDirCliLocal_in);
			oci_bind_by_name($stid, ':cRucCliPed_in', $cRucCliPed_in);
			oci_bind_by_name($stid, ':cUsuModPedVtaCab_in', $cUsuModPedVtaCab_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Datos de cliente pedido actualizado', null);
		} catch (\Exception $e) {
			return CustomResponse::failure($e->getMessage());
		}
	}

	function obtenerInfoPedido(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedDiario_in = $request->input('cNumPedDiario_in');
		$cFecPedVta_in = $request->input('cFecPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedDiario_in' => 'required',
			//            'cFecPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_OBTIENE_INFO_PEDIDO(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedDiario_in => :cNumPedDiario_in,
                cFecPedVta_in => :cFecPedVta_in);end;');
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedDiario_in', $cNumPedDiario_in);
			oci_bind_by_name($stid, ':cFecPedVta_in', $cFecPedVta_in);
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
								'NUM_PED_VTA' => $datos[0],
								'VAL_NETO_PED' => $datos[1],
							]
						);
					}
				}
			}
			oci_free_statement($stid);
			oci_free_statement($cursor);
			oci_close($conn);

			return CustomResponse::success('Informacion de pedido', $lista);
		} catch (\Exception $e) {
			return CustomResponse::failure($e->getMessage());
		}
	}

	function getTiposDeMoneda()
	{
		try {
			$data = DB::table('MAE_MONEDA')
				->select('*')
				->where('FLG_ACTIVO', '=', '1')
				->get();

			return CustomResponse::success('Tipos de moneda listado', $data);
		} catch (\Exception $e) {
			error_log($e);
			return CustomResponse::failure($e->getMessage());
		}
	}


	function setDatosCompElectronico(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');


		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$stid = oci_parse($conn, 'begin SVB_FE_COBRO.SET_DATOS_COMP_ELECTRONICO(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Datos de comprobante electronico guardados');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function asignarHoraSugerida(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$stid = oci_parse($conn, 'begin HHC_CAJA_HHSUR.P_ASIGNAR_HORA_SUGERIDA(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Hora sugerida asignada');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function getPrincActProd(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodProd_in = $request->input('codProd');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codProd' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_OBTIENE_PRINC_ACT_PROD(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodProd_in => :cCodProd_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodProd_in', $cCodProd_in);
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
								'COD_PRINC_ACT' => $datos[0],
								'DESC_PRINC_ACT' => $datos[1],
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Producto principal obtenido', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function getInfoComplProd(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cCodProd_in = $request->input('codProd');
		$cIndVerificaSug = $request->input('cIndVerificaSug');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'codProd' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := PTOVENTA_VTA.VTA_OBTIENE_INFO_COMPL_PROD(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cCodProd_in => :cCodProd_in,
								cIndVerificaSug => :cIndVerificaSug);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cCodProd_in', $cCodProd_in);
			oci_bind_by_name($stid, ':cIndVerificaSug', $cIndVerificaSug);
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
								'stk_Prod' => $datos[0],
								'desc_Acc_Terap' => $datos[1],
								'stk_Prod_Fecha_Actual' => $datos[2],
								'val_Prec_Vta' => $datos[3],
								'unid_Vta' => $datos[4],
								'val_Bono' => $datos[5],
								'porc_Dcto_1' => $datos[6],
								'val_Prec_Lista' => $datos[7],
								'9' => $datos[8],
								'10' => $datos[9],
								'indZan' => $datos[10],
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_close($conn);


			return CustomResponse::success('Producto complementario obtenido', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}



	function impCompElectWS(Request $request)
	{
		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');
		$vSecCompPago_in = $request->input('secCompPago');
		$vVersion_in = $request->input('version');
		$vReimpresion = $request->input('reimpresion');
		$valorAhorro_in = $request->input('valorAhorro');
		$cDocTarjetaPtos_in = $request->input('docTarjetaPtos');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
			'secCompPago' => 'required',
			'version' => 'required',
			'reimpresion' => 'required',
			// 'valorAhorro' => 'required',
			// 'docTarjetaPtos' => 'required',
		]);


		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = '';

			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.IMP_COMP_ELECT_WS(
								vCodGrupoCia_in => :vCodGrupoCia_in,
								vCodLocal_in => :vCodLocal_in,
								vNumPedVta_in => :vNumPedVta_in,
								vSecCompPago_in => :vSecCompPago_in,
								vVersion_in => :vVersion_in,
								vReimpresion => :vReimpresion,
								valorAhorro_in => :valorAhorro_in,
								cDocTarjetaPtos_in => :cDocTarjetaPtos_in);end;');

			oci_bind_by_name($stid, ':vCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':vCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':vNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':vSecCompPago_in', $vSecCompPago_in);
			oci_bind_by_name($stid, ':vVersion_in', $vVersion_in);
			oci_bind_by_name($stid, ':vReimpresion', $vReimpresion);
			oci_bind_by_name($stid, ':valorAhorro_in', $valorAhorro_in);
			oci_bind_by_name($stid, ':cDocTarjetaPtos_in', $cDocTarjetaPtos_in);
			oci_bind_by_name($stid, ':result', $cursor, 50);
			oci_execute($stid);

			oci_close($conn);

			return CustomResponse::success('Peticion exitosa', $cursor);
		} catch (\Throwable $th) {
			if (str_contains($th->getMessage(), '20989'))
				return CustomResponse::failure('COMPROBANTE ELECTRONICO NO CUENTA CON NRO DE CORRELATIVO. COMUNIQUESE CON MESA DE AYUDA.');
			return CustomResponse::failure($th->getMessage());
		}
	}

	function obtieneDocImprimirWs(Request $request)
	{
		$IdDocumento = $request->input('IdDocumento');

		$validator = Validator::make($request->all(), [
			'IdDocumento' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.OBTIENE_DOC_IMPRIMIR_WS(
								IdDocumento => :IdDocumento);end;');

			oci_bind_by_name($stid, ':IdDocumento', $IdDocumento);
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
								'ORDEN' => $datos[0],
								'VALOR' => $datos[1],
								'TIPO_DATO' => $datos[2],
								'TAMANIO' => $datos[3],
								'ALINEACION' => $datos[4],
								'NEGRITA' => $datos[5],
								'SUBRAYADO' => $datos[6],
								'INTERLINEADO' => $datos[7],
								'COLOR_INVERSO' => $datos[8],
								'SALTO_LINEA' => $datos[9],
								'LON_PTERMICO' => $datos[10],
							]
						);
					}
				}
			}

			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Peticion exitosa', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function clearCacheImprimirWs(Request $request)
	{
		$IdDocumento = $request->input('IdDocumento');

		$validator = Validator::make($request->all(), [
			'IdDocumento' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$stid = oci_parse($conn, 'begin HHC_IMP_ELECTRONICO.ClearCacheIMPRIMIR_WS(
								IdDocumento => :IdDocumento);end;');

			oci_bind_by_name($stid, ':IdDocumento', $IdDocumento);
			oci_execute($stid);
			oci_free_statement($stid);
			oci_close($conn);

			return CustomResponse::success('Peticion exitosa');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function obtieneNumCompPagoImpr(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPed_in = $request->input('numPed');
		$cSecCompPago_in = $request->input('secCompPago');
		$cSecImprLocal_in = $request->input('secImprLocal');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPed' => 'required',
			'secCompPago' => 'required',
			'secImprLocal' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = '';

			$stid = oci_parse($conn, 'begin :result := SVB_FE_COBRO.CAJ_OBTIENE_NUM_COMP_PAGO_IMPR(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPed_in => :cNumPed_in,
								cSecCompPago_in => :cSecCompPago_in,
								cSecImprLocal_in => :cSecImprLocal_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPed_in', $cNumPed_in);
			oci_bind_by_name($stid, ':cSecCompPago_in', $cSecCompPago_in);
			oci_bind_by_name($stid, ':cSecImprLocal_in', $cSecImprLocal_in);
			oci_bind_by_name($stid, ':result', $cursor, 20);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Peticion Exitosa', $cursor);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function actualizaEstadoPedido(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('numPedVta');
		$cEstPedVta_in = $request->input('estPedVta');
		$cUsuModPedVtaCab_in = $request->input('usuModPedVtaCab');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
			'estPedVta' => 'required',
			'usuModPedVtaCab' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$stid = oci_parse($conn, 'begin PTOVENTA_CAJ.CAJ_ACTUALIZA_ESTADO_PEDIDO(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in,
								cEstPedVta_in => :cEstPedVta_in,
								cUsuModPedVtaCab_in => :cUsuModPedVtaCab_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
			oci_bind_by_name($stid, ':cEstPedVta_in', $cEstPedVta_in);
			oci_bind_by_name($stid, ':cUsuModPedVtaCab_in', $cUsuModPedVtaCab_in);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Peticion Exitosa');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function infoDetalleAgrupacion(Request $request)
	{
		$cCodGrupoCia_in = $request->input('codGrupoCia');
		$cCodLocal_in = $request->input('codLocal');
		$cNumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CAJ.CAJ_INFO_DETALLE_AGRUPACION(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in);end;');

			oci_bind_by_name($stid, ':cCodGrupoCia_in', $cCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $cCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $cNumPedVta_in);
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
								'COUNT' => $datos[0],
								'SEC_COMP_PAGO' => $datos[1],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Peticion Exitosa', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function imprimirDetalle(Request $request)
	{
		$CodGrupoCia_in = $request->input('codGrupoCia');
		$CodLocal_in = $request->input('codLocal');
		$NumPedVta_in = $request->input('numPedVta');
		$SecCompPago_in = $request->input('secCompPago');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
			'secCompPago' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.IMPRIMIR_DETALLE_WS(
								vCodGrupoCia_in => :vCodGrupoCia_in,
								vCodLocal_in => :vCodLocal_in,
								vNumPedVta_in => :vNumPedVta_in,
								vSecCompPago_in => :vSecCompPago_in);end;');

			oci_bind_by_name($stid, ':vCodGrupoCia_in', $CodGrupoCia_in);
			oci_bind_by_name($stid, ':vCodLocal_in', $CodLocal_in);
			oci_bind_by_name($stid, ':vNumPedVta_in', $NumPedVta_in);
			oci_bind_by_name($stid, ':vSecCompPago_in', $SecCompPago_in);
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
								'COD_PROD' => $datos[0],
								'DESCRIPCION' => $datos[1],
								'UNID' => $datos[2],
								'LAB' => $datos[3],
								'CANT' => $datos[4],
								'PREC_UNIT' => $datos[5],
								'DESCTO' => $datos[6],
								'SUBTOTAL' => $datos[7],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Peticion Exitosa', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function getMetodosPagoImprimir(Request $request)
	{
		$CodGrupoCia_in = $request->input('codGrupoCia');
		$CodLocal_in = $request->input('codLocal');
		$NumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.METODO_PAGO_IMPRIMIR_WS(
                vNumPedVta_in => :vNumPedVta_in,
                vCodGrupoCia_in => :vCodGrupoCia_in,
                vCodLocal_in => :vCodLocal_in);end;');
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_bind_by_name($stid, ':vNumPedVta_in', $NumPedVta_in);
			oci_bind_by_name($stid, ':vCodGrupoCia_in', $CodGrupoCia_in);
			oci_bind_by_name($stid, ':vCodLocal_in', $CodLocal_in);
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
								'COD_FORMA_PAGO' => $datos[0],
								'COD_TIPO_MONEDA' => $datos[1],
								'IMP_PAGO' => $datos[2],
								'VUELTO' => $datos[3],
								'VAL_TIP_CAMBIO' => $datos[4],
								'DESC_FORMA_PAGO' => $datos[5],
								'IMP_TOTAL_PAGO' => $datos[6],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Metodos de pago para imprimir', $lista);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function impCompElect(Request $request)
	{
		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');
		$vSecCompPago_in = $request->input('secCompPago');
		$vVersion_in = $request->input('version');
		$vReimpresion = $request->input('reimpresion');
		$valorAhorro_in = $request->input('valorAhorro');
		$cDocTarjetaPtos_in = $request->input('docTarjetaPtos');
		$cNumOrdenVta_in = $request->input('numOrden');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
			'secCompPago' => 'required',
			'version' => 'required',
			'reimpresion' => 'required',
			'numOrden' => 'required',
			// 'valorAhorro' => 'required',
			// 'docTarjetaPtos' => 'required',
		]);


		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();
			$cursor = '';

			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.IMP_COMP_LABORATORIO_WS(
                    vCodGrupoCia_in => :vCodGrupoCia_in,
                    vCodLocal_in => :vCodLocal_in,
                    vNumPedVta_in => :vNumPedVta_in,
                    vSecCompPago_in => :vSecCompPago_in,
                    vVersion_in => :vVersion_in,
                    vReimpresion => :vReimpresion,
                    valorAhorro_in => :valorAhorro_in,
                    cDocTarjetaPtos_in => :cDocTarjetaPtos_in,
                    cNumOrdenVta_in => :cNumOrdenVta_in);end;');

			oci_bind_by_name($stid, ':vCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':vCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':vNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':vSecCompPago_in', $vSecCompPago_in);
			oci_bind_by_name($stid, ':vVersion_in', $vVersion_in);
			oci_bind_by_name($stid, ':vReimpresion', $vReimpresion);
			oci_bind_by_name($stid, ':valorAhorro_in', $valorAhorro_in);
			oci_bind_by_name($stid, ':cDocTarjetaPtos_in', $cDocTarjetaPtos_in);
			oci_bind_by_name($stid, ':cNumOrdenVta_in', $cNumOrdenVta_in);
			oci_bind_by_name($stid, ':result', $cursor, 50);
			oci_execute($stid);

			return CustomResponse::success('Peticion exitosa', $cursor);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	function getnumOrdenVta(Request $request)
	{
		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);


		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);
			$stid = oci_parse($conn, 'begin :result := HHC_IMP_ELECTRONICO.GET_LISTA_ORDEN_VTA(
                cCodGrupoCia_in => :cCodGrupoCia_in,
                cCodLocal_in => :cCodLocal_in,
                cNumPedVta_in => :cNumPedVta_in);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':result', $cursor, -1, OCI_B_CURSOR);
			oci_execute($stid);
			oci_execute($cursor);

			$lista = [];

			if ($stid) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					array_push($lista, $row);
				}
			}

			oci_close($conn);

			return CustomResponse::success('Numero de orden', $lista);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}


	function impCabecera(Request $request)
	{
		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin PTOVENTA_JASPER_ELECTRONICO.SP_DATA_DOCUMENTO_CAB_FB(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in,
							  datosCabecera => :datosCabecera);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':datosCabecera', $cursor, -1, OCI_B_CURSOR);
			oci_execute($stid);
			oci_execute($cursor);

			$lista = [];

			if ($stid) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					array_push($lista, $row);
				}
			}

			oci_close($conn);

			return CustomResponse::success('Cabecera', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function impDetalle(Request $request)
	{
		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {

			$conn = OracleDB::getConnection();

			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin PTOVENTA_JASPER_ELECTRONICO.SP_DATA_DOCUMENTO_DET_FB(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in,
							  datosDetalleItem => :datosDetalleItem);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':datosDetalleItem', $cursor, -1, OCI_B_CURSOR);
			oci_execute($stid);
			oci_execute($cursor);

			$lista = [];

			if ($stid) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					$item = [];
					foreach ($row as $key => $value) {
						array_push($item, $value);
					}
					array_push($lista, $item);
				}
			}

			oci_close($conn);

			return CustomResponse::success('Detalles', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function generarReporte(Request $request)
	{

		$vCodGrupoCia_in = $request->input('codGrupoCia');
		$vCodLocal_in = $request->input('codLocal');
		$vNumPedVta_in = $request->input('numPedVta');

		$validator = Validator::make($request->all(), [
			'codGrupoCia' => 'required',
			'codLocal' => 'required',
			'numPedVta' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			// $input = __DIR__ . '/reportes/documentElectronicFB.jrxml';

			// $jasper = new PHPJasper;
			// $x= $jasper->compile($input)->output();

			/**
			 *
			 */

			$conn = OracleDB::getConnection();

			$cursor = oci_new_cursor($conn);

			$stid = oci_parse($conn, 'begin PTOVENTA_JASPER_ELECTRONICO.SP_DATA_DOCUMENTO_CAB_FB(
								cCodGrupoCia_in => :cCodGrupoCia_in,
								cCodLocal_in => :cCodLocal_in,
								cNumPedVta_in => :cNumPedVta_in,
							  datosCabecera => :datosCabecera);end;');
			oci_bind_by_name($stid, ':cCodGrupoCia_in', $vCodGrupoCia_in);
			oci_bind_by_name($stid, ':cCodLocal_in', $vCodLocal_in);
			oci_bind_by_name($stid, ':cNumPedVta_in', $vNumPedVta_in);
			oci_bind_by_name($stid, ':datosCabecera', $cursor, -1, OCI_B_CURSOR);
			oci_execute($stid);
			oci_execute($cursor);

			$lista = [];

			if ($stid) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						array_push($lista, $value);
					}
				}
			}

			oci_close($conn);

			/**
			 *
			 */

			$RUC_EMISOR = 20555875828;

			$data = $RUC_EMISOR . '|' . $lista[0] . '|' . str_replace('-', '|', $lista[2]) . '|' . $lista[14] . '|' . $lista[15] . '|' . $lista[8] . '|' . $lista[5] . '|' . $lista[6];

			$url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $data;

			Storage::put('qr.png', file_get_contents($url));

			$fechaDocumento = $lista[8];
			$fechaDocumento = str_replace('-', '', $fechaDocumento);
			$HHmmssFechaActual = date('His');
			$nameDir = $RUC_EMISOR . '_' . $lista[2] . '_' . $fechaDocumento  . '_' . $HHmmssFechaActual;
			$input = __DIR__ . '\\reportes\\documentElectronicFB.jasper';
			$output = public_path() . '\\documentos\\' . $nameDir;
			$imagen = __DIR__ . '\\reportes\\IconBiensalud.jpg';

			$options = [
				'format' => ['pdf'],
				'params' => [
					'RUTA_IMAGEN' => $imagen,
					'RAZON_SOCIAL_EMISOR' => 'CONSORCIO SALUD LIMA SUR',
					'DIRECCION_EMISOR' => 'PR GRAL MIGUEL IGLESIAS NRO. 997',
					'DEP_PROV_DIST_EMISOR' => 'LIMA - LIMA - SAN JUAN DE MIRAFLORES',
					'TELEFONO_EMISOR' => 7178060,
					'FAX_EMISOR' => '-',
					'WEB_EMISOR' => '-',
					'CORREO_EMISOR' => 'consorciohumanidadlimasur@gmail.com',
					'RUC_EMISOR' => $RUC_EMISOR,
					'TITULO_DOCUMENTO' => $lista[1],
					'NUMERO_DOCUMENTO' => $lista[2],
					'RAZON_SOCIAL_CLIENTE' => $lista[3],
					'DIRECCION_CLIENTE' => $lista[4],
					'RUC_CLIENTE' => $lista[6],
					'MONEDA_PAGO_DOCUMENTO' => $lista[7],
					'FECHA_EMISION_DOCUMENTO' => $lista[8],
					'CONDICION_PAGO_DOCUMENTO' => $lista[9],
					'MONTO_EXONERADO' => $lista[10],
					'MONTO_INAFECTO'	=> $lista[11],
					'MONTO_GRATUITO' => $lista[12],
					'MONTO_GRAVADO' => $lista[13],
					'MONTO_IGV' => $lista[14],
					'MONTO_TOTAL' => $lista[15],
					'MONTO_TOTAL_LETRAS' => $lista[17],
					'PORTALFE' => 'http://www.factelectronica.consorciosaludlimasur.com',
					'CODEQR' => storage_path('app/qr.png'),
				]
			];

			$jasper = new PHPJasper;

			$jasper->process(
				$input,
				$output,
				$options
			)->execute();

			return CustomResponse::success('PDF GENERADO', $nameDir . '.pdf');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function subirComprobante(Request $request)
	{
		$nombreComprobante = $request->input('nombreComprobante');

		$validator = Validator::make($request->all(), [
			'nombreComprobante' => 'required',
			'pdf' => 'required|mimes:pdf',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			// $imagenFirma = $codMedi . '.' . $request->imagen->extension();
			$request->pdf->move(public_path('documentos/'), $nombreComprobante);
			return CustomResponse::success('COMPROBANTE SUBIDO');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function getCorrelativoMontoNeto(Request $request)
	{

		$cCodGrupoCia = $request->input('cCodGrupoCia_in');
		$cCodLocal = $request->input('cCod_Local_in');
		$cTipoComp = $request->input('cTipo_Comp_in');
		$cMontoNeto = $request->input('cMonto_Neto_in');
		$cNumCompPago = $request->input('cNum_Comp_Pago_in');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia_in' => 'required',
			'cCod_Local_in' => 'required',
			'cTipo_Comp_in' => 'required',
			'cMonto_Neto_in' => 'required',
			'cNum_Comp_Pago_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {

			$conn =  OracleDB::getConnection();
			$result = '';

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_VTA.F_GET_CORRELATIVO_MONTO_NETO(
				cCodGrupoCia_in=> :cCodGrupoCia_in,
				cCod_Local_in=> :cCod_Local_in,
				cTipo_Comp_in=> :cTipo_Comp_in,
				cMonto_Neto_in=> :cMonto_Neto_in,
				cNum_Comp_Pago_in=> :cNum_Comp_Pago_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCod_Local_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cTipo_Comp_in", $cTipoComp);
			oci_bind_by_name($stmt, ":cMonto_Neto_in", $cMontoNeto);
			oci_bind_by_name($stmt, ":cNum_Comp_Pago_in", $cNumCompPago);
			oci_bind_by_name($stmt, ":result", $result, 100);
			oci_execute($stmt);
			oci_free_statement($stmt);
			oci_close($conn);


			return CustomResponse::success('CORRELATIVO OBTENIDO', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function cajVerificaProdVirtuales(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia_in');
		$cCodLocal = $request->input('cCodLocal_in');
		$cNumPedVta = $request->input('cNumPedVta_in');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia_in' => 'required',
			'cCodLocal_in' => 'required',
			'cNumPedVta_in' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();
			$result = '';

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ.CAJ_VERIFICA_PROD_VIRTUALES(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumPedVta_in=> :cNumPedVta_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumPedVta_in", $cNumPedVta);
			oci_bind_by_name($stmt, ":result", $result, 100);

			oci_execute($stmt);
			oci_free_statement($stmt);
			oci_close($conn);

			return CustomResponse::success('PRODUCTOS VIRTUALES VERIFICADOS', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cajVerificaPedido(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumPedVta = $request->input('cNumPedVta');
		$nMontoVta = $request->input('nMontoVta');
		$nIndReclamoNavsat = $request->input('nIndReclamoNavsat');
		$cIndAnulaTodoPedido = $request->input('cIndAnulaTodoPedido');
		$cValMints = $request->input('cValMints');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumPedVta' => 'required',
			'nMontoVta' => 'required',
			// 'nIndReclamoNavsat' => 'required',
			// 'cIndAnulaTodoPedido' => 'required',
			// 'cValMints' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}


		try {
			$conn =  OracleDB::getConnection();

			$stmt = ociparse($conn, "BEGIN PTOVENTA_CAJ_ANUL.CAJ_VERIFICA_PEDIDO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumPedVta_in=> :cNumPedVta_in,
			nMontoVta_in=> :nMontoVta_in,
			nIndReclamoNavsat_in=> :nIndReclamoNavsat_in,
			cIndAnulaTodoPedido_in=> :cIndAnulaTodoPedido_in,
			cValMints_in=> :cValMints_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumPedVta_in", $cNumPedVta);
			oci_bind_by_name($stmt, ":nMontoVta_in", $nMontoVta);
			oci_bind_by_name($stmt, ":nIndReclamoNavsat_in", $nIndReclamoNavsat);
			oci_bind_by_name($stmt, ":cIndAnulaTodoPedido_in", $cIndAnulaTodoPedido);
			oci_bind_by_name($stmt, ":cValMints_in", $cValMints);

			oci_execute($stmt);
			oci_free_statement($stmt);
			oci_close($conn);

			return CustomResponse::success('PEDIDO VERIFICADO');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cajListaCabeceraPedido(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumPedVta = $request->input('cNumPedVta');
		$cNumCompPag = $request->input('cNumCompPag');
		$cFlagTipProcPago = $request->input('cFlagTipProcPago');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumPedVta' => 'required',
			'cNumCompPag' => 'required',
			'cFlagTipProcPago' => 'required',
		]);


		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ_ANUL.CAJ_LISTA_CABECERA_PEDIDO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumPedVta_in=> :cNumPedVta_in,
			cNumCompPag=> :cNumCompPag,
			cFlagTipProcPago=> :cFlagTipProcPago); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumPedVta_in", $cNumPedVta);
			oci_bind_by_name($stmt, ":cNumCompPag", $cNumCompPag);
			oci_bind_by_name($stmt, ":cFlagTipProcPago", $cFlagTipProcPago);
			oci_bind_by_name($stmt, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_execute($stmt);
			oci_execute($cursor);

			$lista = [];

			if ($stmt) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						$datos = explode('Ã', $value);

						array_push(
							$lista,
							[
								'FECHA' => $datos[1],
								'TOTAL' => $datos[2],
								'RUC' => $datos[3],
								'CLIENTE' => $datos[4],
								'CAJERO' => $datos[5],
								'CONVENIO' => $datos[6],
								'key' => $datos[0],
								'7' => $datos[7],
								'8' => $datos[8],
								'9' => $datos[9],
								'10' => $datos[10],
								'11' => $datos[11],

							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Datos Obtenidos', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cajListaDetallePedido(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumPedVta = $request->input('cNumPedVta');
		$cTipComp = $request->input('cTipComp');
		$cNumComp = $request->input('cNumComp');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumPedVta' => 'required',
			'cTipComp' => 'required',
			'cNumComp' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ_ANUL.CAJ_LISTA_DETALLE_PEDIDO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumPedVta_in=> :cNumPedVta_in,
			cTipComp_in=> :cTipComp_in,
			cNumComp_in=> :cNumComp_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumPedVta_in", $cNumPedVta);
			oci_bind_by_name($stmt, ":cTipComp_in", $cTipComp);
			oci_bind_by_name($stmt, ":cNumComp_in", $cNumComp);
			oci_bind_by_name($stmt, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_execute($stmt);
			oci_execute($cursor);


			$lista = [];

			if ($stmt) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						$datos = explode('Ã', $value);

						array_push(
							$lista,
							[
								'CODIGO' => $datos[0],
								'key' => $datos[0],
								'DESCRIPCION' => $datos[1],
								'UNIDAD' => $datos[2],
								'PRE_VENTA' => $datos[3],
								'CANT' => $datos[4],
								'TOTAL' => $datos[5],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Datos Obtenidos', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	// ------------------------------


	function cajListaDetalleNotaCredito(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumPedVta = $request->input('cNumPedVta');
		$cTipComp = $request->input('cTipComp');
		$cNumComp = $request->input('cNumComp');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumPedVta' => 'required',
			'cTipComp' => 'required',
			'cNumComp' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ_ANUL.CAJ_LISTA_DETALLE_NOTA_CREDITO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumPedVta_in=> :cNumPedVta_in,
			cTipComp_in=> :cTipComp_in,
			cNumComp_in=> :cNumComp_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumPedVta_in", $cNumPedVta);
			oci_bind_by_name($stmt, ":cTipComp_in", $cTipComp);
			oci_bind_by_name($stmt, ":cNumComp_in", $cNumComp);
			oci_bind_by_name($stmt, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_execute($stmt);
			oci_execute($cursor);

			$lista = [];

			if ($stmt) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						$datos = explode('Ã', $value);

						array_push(
							$lista,
							[
								'0' => $datos[0],
								'1' => $datos[1],
								'2' => $datos[2],
								'3' => $datos[3],
								'4' => $datos[4],
								'5' => $datos[5],
								'6' => $datos[6],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Datos Obtenidos', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}



	function cajListaCajaUsuario(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}


		try {
			$conn =  OracleDB::getConnection();
			$cursor = oci_new_cursor($conn);

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ_ANUL.CAJ_LISTA_CAJA_USUARIO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);

			oci_bind_by_name($stmt, ":result", $cursor, -1, OCI_B_CURSOR);
			oci_execute($stmt);
			oci_execute($cursor);

			$lista = [];

			if ($stmt) {
				while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
					foreach ($row as $key => $value) {
						$datos = explode('Ã', $value);

						array_push(
							$lista,
							[
								'0' => $datos[0],
								'1' => $datos[1],
								'2' => $datos[2],
								'3' => $datos[3],
								'4' => $datos[4],
								'5' => $datos[5],
								'6' => $datos[6],
							]
						);
					}
				}
			}

			oci_close($conn);

			return CustomResponse::success('Datos Obtenidos', $lista);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function cajAgregarCabNotaCredito(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumVtaAnt = $request->input('cNumVtaAnt');
		$nTipoCam = $request->input('nTipoCam');
		$vIdUsu = $request->input('vIdUsu');
		$nNumCajaPago = $request->input('nNumCajaPago');
		$cMotivoAnulacion = $request->input('cMotivoAnulacion');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumVtaAnt' => 'required',
			'nTipoCam' => 'required',
			'vIdUsu' => 'required',
			'nNumCajaPago' => 'required',
			'cMotivoAnulacion' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();
			$result = '';

			$stmt = ociparse($conn, "BEGIN :result := PTOVENTA_CAJ_ANUL.CAJ_AGREGAR_CAB_NOTA_CREDITO(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumVtaAnt_in=> :cNumVtaAnt_in,
			nTipoCam_in=> :nTipoCam_in,
			vIdUsu_in=> :vIdUsu_in,
			nNumCajaPago_in=> :nNumCajaPago_in,
			cMotivoAnulacion_in=> :cMotivoAnulacion_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumVtaAnt_in", $cNumVtaAnt);
			oci_bind_by_name($stmt, ":nTipoCam_in", $nTipoCam);
			oci_bind_by_name($stmt, ":vIdUsu_in", $vIdUsu);
			oci_bind_by_name($stmt, ":nNumCajaPago_in", $nNumCajaPago);
			oci_bind_by_name($stmt, ":cMotivoAnulacion_in", $cMotivoAnulacion);
			oci_bind_by_name($stmt, ":result", $result, 100);
			oci_execute($stmt);

			oci_close($conn);

			return CustomResponse::success('Datos Obtenidos', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	function cajAgregarDetNotaCredito(Request $request)
	{
		$cCodGrupoCia = $request->input('cCodGrupoCia');
		$cCodLocal = $request->input('cCodLocal');
		$cNumVtaAnt = $request->input('cNumVtaAnt');
		$cNumVta = $request->input('cNumVta');
		$cCodProd = $request->input('cCodProd');
		$nCantProd = $request->input('nCantProd');
		$nTotal = $request->input('nTotal');
		$vIdUsu = $request->input('vIdUsu');
		$nSecDetPed = $request->input('nSecDetPed');
		$nNumCajaPago = $request->input('nNumCajaPago');

		$validator = Validator::make($request->all(), [
			'cCodGrupoCia' => 'required',
			'cCodLocal' => 'required',
			'cNumVtaAnt' => 'required',
			'cNumVta' => 'required',
			'cCodProd' => 'required',
			'nCantProd' => 'required',
			'nTotal' => 'required',
			'vIdUsu' => 'required',
			'nSecDetPed' => 'required',
			'nNumCajaPago' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			$conn =  OracleDB::getConnection();

			$stmt = ociparse($conn, "BEGIN PTOVENTA_CAJ_ANUL.CAJ_AGREGAR_DET_NC(
			cCodGrupoCia_in=> :cCodGrupoCia_in,
			cCodLocal_in=> :cCodLocal_in,
			cNumVtaAnt_in=> :cNumVtaAnt_in,
			cNumVta_in=> :cNumVta_in,
			cCodProd_in=> :cCodProd_in,
			nCantProd_in=> :nCantProd_in,
			nTotal_in=> :nTotal_in,
			vIdUsu_in=> :vIdUsu_in,
			nSecDetPed_in=> :nSecDetPed_in,
			nNumCajaPago_in=> :nNumCajaPago_in); END;");

			oci_bind_by_name($stmt, ":cCodGrupoCia_in", $cCodGrupoCia);
			oci_bind_by_name($stmt, ":cCodLocal_in", $cCodLocal);
			oci_bind_by_name($stmt, ":cNumVtaAnt_in", $cNumVtaAnt);
			oci_bind_by_name($stmt, ":cNumVta_in", $cNumVta);
			oci_bind_by_name($stmt, ":cCodProd_in", $cCodProd);
			oci_bind_by_name($stmt, ":nCantProd_in", $nCantProd);
			oci_bind_by_name($stmt, ":nTotal_in", $nTotal);
			oci_bind_by_name($stmt, ":vIdUsu_in", $vIdUsu);
			oci_bind_by_name($stmt, ":nSecDetPed_in", $nSecDetPed);
			oci_bind_by_name($stmt, ":nNumCajaPago_in", $nNumCajaPago);
			oci_execute($stmt);

			oci_close($conn);
			return CustomResponse::success('Correcto');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function validaCambioPrecio(Request  $request)
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
			$result = '';
			$stid = oci_parse($conn, 'begin :result := FARMA_UTILITY.IS_VALIDA_CAMBIO_PRECIO(
                vCodGrupoCia_in => :vCodGrupoCia_in,
                vCodLocal_in => :vCodLocal_in,
                vSecUsu_local_in => :vSecUsu_local_in);end;');
			oci_bind_by_name($stid, ':result', $result, 5);
			oci_bind_by_name($stid, ':vCodGrupoCia_in', $cNumCMP_in);
			oci_bind_by_name($stid, ':vCodLocal_in', $cValor);
			oci_bind_by_name($stid, ':vSecUsu_local_in', $cCodUsu);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Respuesta correcta', $result);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure($th->getMessage());
		}
	}

	public function existeCliente(Request $request)
	{
		$numDoc = $request->input('numDoc');

		$validator = Validator::make($request->all(), [
			'numDoc' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		}

		try {
			$conn = OracleDB::getConnection();
			$result = '';

			$stid = oci_parse($conn, 'begin :result := PTOVENTA_CLI.CLI_AGREGA_VALIDA_CLI_NATURAL(
                cNumDocIdent_in => :cNumDocIdent_in);end;');
			oci_bind_by_name($stid, ':result', $result, 5);
			oci_bind_by_name($stid, ':cNumDocIdent_in', $numDoc);
			oci_execute($stid);
			oci_close($conn);

			return CustomResponse::success('Procedimiento correcto', $result);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	function downloadComprobante(Request $request)
	{
		$nombreArchivo = $request->input('nombreArchivo');
		$file = public_path('documentos/' . $nombreArchivo);
		return response()->download($file);
	}
}
