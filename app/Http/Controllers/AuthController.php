<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\UsuarioNivel;
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
