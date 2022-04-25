<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Rol;
use App\Models\Roles;
use GrahamCampbell\ResultType\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
	/**
	 * Inicio de sesión
	 * 
	 * @OA\Post(
	 *     path="/historial-clinico-backend/public/api/login",
	 *     tags={"Autenticación"},
	 *     operationId="login",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="application/json",
	 *             @OA\Schema(
	 *               required={
	 *                  "nroCMP",
	 *                  "nroDoc"
	 *               },
	 *                 @OA\Property(
	 *                     property="nroCMP",
	 *                     type="string",
	 *                 ),
	 *                 @OA\Property(
	 *                     property="nroDoc",
	 *                     type="string",
	 *                 ),
	 *                 example={
	 *                  "nroCMP": "25480",
	 *                  "nroDoc": "41686893"
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
	public function login(Request $request)
	{
		$nroCMP = $request->input('nroCMP');
		$nroDoc = $request->input('nroDoc');
		$validator = Validator::make($request->all(), [
			'nroCMP' => 'required',
			'nroDoc' => 'required'
		]);
		$errorResponse = response()->json(
			[
				'success' => false,
				'message' => 'CMP/Documento incorrectos'
			]
		);
		if ($validator->fails()) {
			return response()->json(
				[
					'success' => false,
					'message' => 'Faltan datos'
				]
			);
		} else {
			try {
				$result = null;
				$pdo = DB::getPdo();
				$stmt = $pdo->prepare("BEGIN :result := centro_medico.f_validar_acceso_medico(cnrocmp_in=>:cmp,cnrodoc_in=>:doc); END;");
				$stmt->bindParam(':cmp', $nroCMP, \PDO::PARAM_STR);
				$stmt->bindParam(':doc', $nroDoc, \PDO::PARAM_STR);
				$stmt->bindParam(':result', $result, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT);
				$stmt->execute();
				if ($result) {
					$medico = DB::select('select * from MAE_MEDICO WHERE COD_MEDICO=?', [$result]);
					$data1 = DB::select("select * from cc_medico_x_bus where num_cmp = ?", [$nroCMP]);
					$data2 = DB::select("select * from cc_consultorio where ID_CONSULTORIO =?", [$data1[0]->id_consultorio]);
					$abb = str_pad($result, 10, "0", STR_PAD_LEFT);
					$modulosUsuario = Rol::query()->where(['COD_MEDICO' => $abb])->get();

					$resultado = [];

					foreach ($medico[0] as $key => $value) {
						$resultado[$key] = $value;
					}

					foreach ($data1[0] as $key => $value) {
						$resultado[$key] = $value;
					}

					if ($modulosUsuario) {
						foreach ($modulosUsuario as $key => $value) {
							$resultado['modulos'][] = $value['cod_mod'];
						}
					}
					
					$resultado['des_especialidad'] = $data2[0]->descripcion;

					return response()->json(
						[
							'success' => true,
							'message' => 'Datos encontrados',
							'data' => $resultado,
						]
					);
				} else {
					return $errorResponse;
				}
			} catch (\Throwable $e) {
				return CustomResponse::failure($e->getMessage());
			}
		}
	}

	/**
	 * Obtener código de médico
	 * 
	 * @OA\Post(
	 *     path="/historial-clinico-backend/public/api/getCMP",
	 *     tags={"Autenticación"},
	 *     operationId="getCMP",
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
	 *                 ),
	 *                 example={
	 *                  "codMedico": "0000026144"
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
	public function getCMP(Request $request)
	{
		$codMedico = $request->input('codMedico');
		$validator = Validator::make($request->all(), [
			'codMedico' => 'required',
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('faltan datos');
		}

		try {
			$medico = DB::select('select * from MAE_MEDICO WHERE COD_MEDICO=?', [$codMedico]);

			return CustomResponse::success('Datos Encontrados', $medico);
		} catch (\Throwable $th) {
			return CustomResponse::failure($th->getMessage());
		}
	}
}
