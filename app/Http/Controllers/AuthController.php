<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Rol;
use App\Models\Roles;
use App\Models\UsuarioNivel;
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

	public function loginAdministrador(Request $request) {
		$usuario = $request->input('usuario');
		$clave = $request->input('clave');

		$validator = Validator::make($request->all(), [
			'usuario' => 'required',
			'clave' => 'required'
		]);

		if ($validator->fails()) {
			return CustomResponse::failure('Datos faltantes.');
		}

		try {
			$data = DB::table('HWC_ADM_HC_SEC')
				->where([
					['login_usu', '=', strtoupper($usuario)],
					['clave_usu', '=', $clave]
				])
				->first();

			if (!$data) {
				return CustomResponse::failure('Usuario o clave incorrectos.');
			}
			return CustomResponse::success('Ingreso exitoso', $data);
		} catch (\Throwable $th) {
			error_log($th);
			return CustomResponse::failure('Error en los servidores.');
		}
	}

    function getUsuarioInfoToToken(Request $request) {
        $usuario = $request->input('usuario');
        $clave = $request->input('clave');

        $validator = Validator::make($request->all(), [
            'usuario' => 'required',
            'clave' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        }

        try {
            $data = DB::table('PBL_USU_LOCAL')
                ->where([
                    ['login_usu', '=', strtoupper($usuario)],
                    ['clave_usu', '=', $clave]
                ])
                ->first();

            return CustomResponse::success('Informacion de usuario', $data);
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure();
        }
    }


		public function loginUsuLocal(Request $request)
    {
        $nroGrupo = "001";
        $nroLocal = "001";
        $nroUsuario = $request->input('usuario');
        $nroClave = $request->input('clave');
        $validator = Validator::make($request->all(), [
            'usuario' => 'required',
            'clave' => 'required'
        ]);


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
                $stmt = $pdo->prepare("BEGIN :result := farma_security.verifica_usuario_login(ccodgrupocia_in=>:grupo,ccodlocal_in=>:local,ccodusu_in=>:usuario,cclaveusu_in=>:clave); END;");
                $stmt->bindParam(':grupo', $nroGrupo, \PDO::PARAM_STR);
                $stmt->bindParam(':local', $nroLocal, \PDO::PARAM_STR);
                $stmt->bindParam(':usuario', $nroUsuario, \PDO::PARAM_STR);
                $stmt->bindParam(':clave', $nroClave, \PDO::PARAM_STR);

                $stmt->bindParam(':result', $result, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT);
                $stmt->execute();
                if ($result) {
                    $nivel = [];
                    $resultado = 'Volver a intentar';
                    $success = false;
                    $modelo = UsuarioNivel::where(["LOGIN_USU" => $nroUsuario, "ESTADO" => '1'])
                        ->with(['nivel'])
                        ->get();
                    if ($modelo) {
                        foreach ($modelo as $item) {
                            //$aux[0] = $item['nivel']['descripcion'];
                            //$aux[1] = $item['nivel']['modulo'];
                            array_push($nivel, $item['nivel']['modulo']);
                        }
                    }
                    switch ($result) {
                        case '01':
                            $resultado = 'Usuario OK';
                            $success = true;
                            break;
                        case '02':
                            $resultado = 'Usuario Inactivo en el Local';
                            $success = false;
                            break;
                        case '03':
                            $resultado = 'Usuario no registrado en el Local';
                            $success = false;
                            break;
                        case '04':
                            $resultado = 'Clave Errada';
                            $success = false;
                            break;
                        case '05':
                            $resultado = 'Usuario No Existe';
                            $success = false;
                            break;
                        case '98':
                            $resultado = 'Version de aplicacion no valida';
                            $success = false;
                            break;
                    }
                    return response()->json(
                        [
                            'success' => $success,
                            'message' => $resultado,
                            'modulos' => $nivel
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Usuario o clave incorrecta',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                return CustomResponse::failure($e->getMessage());
            }
        }
    }

}
