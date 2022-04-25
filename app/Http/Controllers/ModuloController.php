<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\ModuloLog;
use App\Models\Modulos;
use App\Models\Rol;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ModuloController extends Controller
{

	public function getModulos(Request $request)
	{
		try {
			$mod = Modulos::select("*")->get();
			return CustomResponse::success("Datos Encontrados", $mod);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}

	public function editarModulos(Request $request)
	{
		$idRol = $request->input('ID_MODULO');
		$nombreRol = $request->input('NOMBRE_MODULO');
		$validator = Validator::make($request->all(), [
			'ID_MODULO' => 'required',
			'NOMBRE_MODULO' => 'required',
		]);
		if ($validator->fails()) {
			return CustomResponse::failure("Datos Faltantes");
		} else {
			try {

				DB::update("update HCW_MODULOS set NOMBRE_MODULO = ? where ID_MODULO = ?", [$nombreRol, $idRol]);

				return CustomResponse::success("Datos Actualizados");
			} catch (\Throwable $th) {
				return CustomResponse::failure($th->getMessage());
			}
		}
	}

	public function eliminarModulos(Request $request)
	{
		$idRol = $request->input('ID_MODULO');
		$validator = Validator::make($request->all(), [
			'ID_MODULO' => 'required'
		]);
		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes');
		} else {
			try {
				// $mod = Rol::where('COD_MEDICO', '=', $idRol);
				$mod  = Modulos::where('ID_MODULO', '=', $idRol);
				$mod->delete();
				return CustomResponse::success();
			} catch (\Throwable $th) {
				return CustomResponse::failure($th->getMessage());
			}
		}
	}

	public function asignacionModulos(Request $request)
	{
		$codMed = $request->input('codMedico');
		$modu = $request->input('modulos');
		$codMed1 = $request->input('codMedico1');
		$validator = Validator::make($request->all(), [
			'codMedico' => 'required',
			'codMedico1' => 'required',
			// 'modulos' => 'required',
		]);
		if ($validator->fails()) {
			return CustomResponse::failure("Datos Faltantes");
		} else {
			try {

				$rol = Rol::select("*")->where(["COD_MEDICO" => $codMed])->get();
				$detalles = [
					"codMedico" => $codMed,
				];

				if (!$modu) {

					// AGREGAR REGISTRO
					$modulosOld = [];
					foreach ($rol as $key => $value) {
						$modulosOld[] = $value->cod_mod;
					}

					$detalles['modulosOld'] = $modulosOld;

					ModuloLog::insert([
						"ID_LOG" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
						"COD_MEDICO" => $codMed1,
						"TIPO" => "Eliminar",
						"FECHA" => date("Y-m-d H:i:s"),
						"DETALLES" => json_encode($detalles),
					]);
					// --------------------

					Rol::query()->where(['COD_MEDICO' => $codMed])->delete();

					return CustomResponse::success("Sin Modulos");
				}
				$medico = DB::select('select * from MAE_MEDICO WHERE COD_MEDICO=?', [$codMed]);
				if ($medico) {
					if (count($rol) > 0) {
						// AGREGAR REGISTRO
						$modulosOld = [];
						foreach ($rol as $key => $value) {
							$modulosOld[] = $value->cod_mod;
						}

						$detalles['modulosOld'] = $modulosOld;

						$detalles['modulosNew'] = $modu;
						ModuloLog::insert([
							"ID_LOG" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
							"COD_MEDICO" => $codMed1,
							"TIPO" => "Editar",
							"FECHA" => date("Y-m-d H:i:s"),
							"DETALLES" => json_encode($detalles),
						]);
						// --------------------

						Rol::query()->where(['COD_MEDICO' => $codMed])->delete();

						foreach ($modu as $key => $value) {
							Rol::insert([
								"cod_medico" => $codMed,
								"cod_mod" => $value,
							]);
						}
					} else {
						// AGREGAR REGISTRO
						$detalles['modulosNew'] = $modu;
						ModuloLog::insert([
							"ID_LOG" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
							"COD_MEDICO" => $codMed1,
							"TIPO" => "Agregar",
							"FECHA" => date("Y-m-d H:i:s"),
							"DETALLES" => json_encode($detalles),
						]);
						// --------------------

						foreach ($modu as $key => $value) {
							Rol::insert([
								"cod_medico" => $codMed,
								"cod_mod" => $value,
							]);
						}
					}

					return CustomResponse::success("Datos Registrados");
				} else {
					return CustomResponse::failure("No Existe Codigo de Medico");
				}
			} catch (\Throwable $th) {
				return CustomResponse::failure($th->getMessage());
			}
		}
	}


	public function getMedicosModulos(Request $request)
	{
		$mod = Rol::select("*")->join('HCW_MODULOS', 'HCW_MODULOS.ID_MODULO', '=', 'HCW_USU_MOD.COD_MOD')->orderBy('cod_medico', 'DESC')->get();

		$codMedicoActual = "";
		$medico = "";
		$medicos = [];

		foreach ($mod as $key => $value) {

			if ($value->cod_medico == $codMedicoActual) {

				$medicos[count($medicos) - 1]['modulos'][$value->cod_mod] = $value->nombre_modulo;
			} else {
				$medico = DB::select('select * from MAE_MEDICO WHERE COD_MEDICO=?', [$value->cod_medico]);
				$meddd = [];
				foreach ($medico[0] as $key => $value1) {
					$meddd[$key] = $value1;
				}

				$meddd['modulos'][$value->cod_mod] = $value->nombre_modulo;
				$meddd['id'] = $value->cod_medico;
				$medico = $meddd;
				$medicos[] = $meddd;
				$codMedicoActual = $value->cod_medico;
			}
		}

		return CustomResponse::success("Datos Encontrados", $medicos);
	}

	/**
	 * Búsqueda de medicos
	 * 
	 * @OA\Post(
	 *     path="/historial-clinico-backend/public/api/modulos/getDataMedicos",
	 *     tags={"Modulo"},
	 *     operationId="getDataMedicos",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *                 @OA\Property(
	 *                     property="num_cmp",
	 *                     type="string"
	 *                 ),
	 *                 @OA\Property(
	 *                     property="des_nom_medico",
	 *                     type="string"
	 *                 ),
	 *                 example={"num_cmp": "144", "des_nom_medico": ""}
	 *             )
	 *         )
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Medicos",     
	 *     )
	 * )
	 */
	public function getDataMedicos(Request $request)
	{
		$num_cmp = $request->input('num_cmp');
		$des_nom_medico = $request->input('des_nom_medico');
		try {
			if ($num_cmp == "" && $des_nom_medico == "") {
				return CustomResponse::failure("Datos Faltantes");
			} elseif ($num_cmp == "" && $des_nom_medico !== "") {
				$model = Medico::select(['num_cmp', 'des_nom_medico', 'des_ape_medico', 'cod_medico'])
					->orWhere(DB::raw("concat(lower(trim(des_nom_medico)), concat(' ' , lower(trim(des_ape_medico))))"), 'like', ['%' . strtolower($des_nom_medico) . '%'])
					// ->whereRaw('lower(des_nom_medico) like (?) ', [strtolower($des_nom_medico) . '%'])
					->limit(20)
					->get();
			} elseif ($des_nom_medico == "" && $num_cmp !== "") {
				$model = Medico::select(['num_cmp', 'des_nom_medico', 'des_ape_medico', 'cod_medico'])
					->where('num_cmp', 'like', "{$num_cmp}%")->limit(20)
					->get();
			} else {
				$model = Medico::select(['num_cmp', 'des_nom_medico', 'des_ape_medico', 'cod_medico'])
					->orWhere(DB::raw("concat(lower(trim(des_nom_medico)), concat(' ' , lower(trim(des_ape_medico))))"), 'like', ['%' . strtolower($des_nom_medico) . '%'])
					// ->whereRaw('lower(des_nom_medico) like (?) ', [strtolower($des_nom_medico) . '%'])
					->orWhere('num_cmp', 'like', "{$num_cmp}%")->limit(20)
					->get();
			}

			if ($model) {
				return CustomResponse::success("Medicos", $model);
			} else {
				return CustomResponse::failure("No Existen Medicos");
			}
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}
}
