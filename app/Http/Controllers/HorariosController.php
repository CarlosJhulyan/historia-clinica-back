<?php

namespace App\Http\Controllers;


use App\Core\CustomResponse;
use App\Models\Horarios;
use App\Oracle\OracleDB;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class HorariosController extends Controller
{

	function setHorario(Request $request)
	{
		$cmp = $request->input('cmp');
		$nombreMedico = $request->input('nombreMedico');
		$fecha = $request->input('fecha');
		$horaInicio = $request->input('horaInicio');
		$horaFin = $request->input('horaFin');
		// $especialidad = $request->input('especialidad');


		$validator = Validator::make($request->all(), [
			'cmp' => 'required',
			'nombreMedico' => 'required',
			'fecha' => 'required',
			'horaInicio' => 'required',
			'horaFin' => 'required',
			// 'especialidad' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Faltan Datos');
		}
		try {

			// $data1 = DB::select("select * from cc_medico_x_bus where num_cmp = ?", [$cmp]);
			// $data2 = DB::select("select * from cc_consultorio where ID_CONSULTORIO =?", [$data1[0]->id_consultorio]);

			$data = DB::insert('INSERT INTO HCW_HORARIOS (ID_HORARIO,CMP,NOMBRE_MEDICO,FECHA,HORA_INICIO,HORA_FIN,ESPECIALIDAD) VALUES(?,?,?,?,?,?,?)', [
				round(((microtime(true)) * 1000)) . 'DT' . uniqid(),
				$cmp,
				$nombreMedico,
				new DateTime($fecha),
				$horaInicio,
				$horaFin,
				'',
				// $data2[0]->descripcion,
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
			return CustomResponse::success('Horarios', $data);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}
}
