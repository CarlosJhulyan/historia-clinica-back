<?php

namespace App\Http\Controllers;


use App\Core\CustomResponse;
use App\Models\Horarios;
use App\Oracle\OracleDB;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class HorariosController extends Controller
{

	public function editarHorario()
	{
		$id = request()->input('id');
		$fecha = request()->input('fecha');
		$horaInicio = request()->input('horaInicio');
		$horaFin = request()->input('horaFin');

		$validator = Validator::make(request()->all(), [
			'id' => 'required',
			'fecha' => 'required',
			'horaInicio' => 'required',
			'horaFin' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			DB::update('UPDATE HCW_HORARIOS SET FECHA = :fecha, HORA_INICIO = :horaInicio, HORA_FIN = :horaFin WHERE ID_HORARIO = :id', [
				'fecha' => new DateTime($fecha),
				'horaInicio' => $horaInicio,
				'horaFin' => $horaFin,
				'id' => $id,
			]);
			return CustomResponse::success('Horario editado correctamente');
		} catch (\Throwable $th) {
			return CustomResponse::failure([
				$th->getMessage(),
				$fecha,
				$horaInicio,
				$horaFin,
				$id
			]);
		}
	}

	public function eliminarHorario()
	{
		$id = request()->input('id');

		$validator = Validator::make(request()->all(), [
			'id' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure($validator->errors()->first());
		}

		try {
			DB::delete('DELETE HCW_HORARIOS WHERE ID_HORARIO = :id', [
				'id' => $id,
			]);
			return CustomResponse::success('Horario editado correctamente');
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	public function getMedicoByEspecialidad()
	{
		$especialidad_id = request()->input('especialidad_id');

		$validator = Validator::make(request()->all(), [
			'especialidad_id' => 'required|numeric',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure("Faltan Datos");
		}

		try {
			$medicos = DB::select('select * from mae_medico mm inner join cc_medico_x_bus mb on mm.num_cmp = mb.num_cmp inner join cc_consultorio cc on mb.id_consultorio = cc.id_consultorio where cc.id_consultorio = ?', [$especialidad_id]);

			return CustomResponse::success('Medicos', $medicos);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	public function obtenerEspecialidad()
	{
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

	function setHorario(Request $request)
	{
		$cmp = $request->input('cmp');
		$nombreMedico = $request->input('nombreMedico');
		$fecha = $request->input('fecha');
		$horaInicio = $request->input('horaInicio');
		$horaFin = $request->input('horaFin');
		$especialidad = $request->input('especialidad');
		$idEspecialidad = $request->input('idEspecialidad');


		$validator = Validator::make($request->all(), [
			'cmp' => 'required',
			'nombreMedico' => 'required',
			'fecha' => 'required',
			'horaInicio' => 'required',
			'horaFin' => 'required',
			'especialidad' => 'required',
			'idEspecialidad' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Faltan Datos');
		}
		try {

			$horario = DB::select("select * from HCW_HORARIOS where CMP = ? and to_char(FECHA,'YYYY-MM-DD') = ? and ID_ESPECIALIDAD = ?", [$cmp, explode('T', $fecha)[0], $idEspecialidad]);
			if (count($horario) > 0) {
				foreach ($horario as $key => $value) {
					$horaI = explode(':', $value->hora_inicio);
					$horaF = explode(':', $value->hora_fin);
					if (intval($horaI[0]) <= intval(explode(':', $horaInicio)[0]) && intval($horaF[0]) >= intval(explode(':', $horaInicio)[0])) {
						return CustomResponse::failure('El medico ya esta ocupado en ese horario');
					}
					if (intval($horaI[0]) <= intval(explode(':', $horaFin)[0]) && intval($horaF[0]) >= intval(explode(':', $horaFin)[0])) {
						return CustomResponse::failure('El medico ya esta ocupado en ese horario');
					}
					if (intval($horaI[0]) >= intval(explode(':', $horaInicio)[0]) && intval($horaF[0]) <= intval(explode(':', $horaFin)[0])) {
						return CustomResponse::failure('El medico ya esta ocupado en ese horario');
					}
					if (intval($horaI[0]) <= intval(explode(':', $horaInicio)[0]) && intval($horaF[0]) >= intval(explode(':', $horaFin)[0])) {
						return CustomResponse::failure('El medico ya esta ocupado en ese horario');
					}
				}
			}

			$data = DB::insert('INSERT INTO HCW_HORARIOS (ID_HORARIO,CMP,NOMBRE_MEDICO,FECHA,HORA_INICIO,HORA_FIN,ESPECIALIDAD,ID_ESPECIALIDAD) VALUES(?,?,?,?,?,?,?,?)', [
				round(((microtime(true)) * 1000)) . 'DT' . uniqid(),
				$cmp,
				$nombreMedico,
				new DateTime($fecha),
				$horaInicio,
				$horaFin,
				$especialidad,
				$idEspecialidad
			]);
			return CustomResponse::success('Horario Agregado', $data);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}


	// GET HORARIOS POR MES
	function getHorarioFecha(Request $request)
	{
		$mes = $request->input('mes');

		$validator = Validator::make($request->all(), [
			'mes' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Faltan Datos');
		}

		try {
			$data = DB::select("select * from HCW_HORARIOS where to_char(FECHA,'MM') = ?", [$mes]);

			foreach ($data as $key => $value) {
				// convertir zona horaria utc a zona America/Lima
				$dd = new DateTime($value->fecha, new DateTimeZone('UTC'));
				$data[$key]->fecha = $dd->setTimezone(new DateTimeZone('America/Lima'))->format('Y-m-d');
			}

			return CustomResponse::success('Horarios', $data);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}
}
