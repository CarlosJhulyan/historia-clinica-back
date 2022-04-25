<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Camas;
use App\Models\CamasHabitaciones;
use App\Models\CamasLog;
use App\Models\CamasPisos;
use App\Models\Pacientes;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CamasController extends Controller
{
  //** ------------------------- Pisos -------------------------*/

  function getPisos(Request $request)
  {
    try {
      $pisos = CamasPisos::select("*")
        ->orderBy("NOMBRE_PISO")
        ->get();
      return CustomResponse::success("pisos", $pisos);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function deletePiso(Request $request)
  {
    $pisoId = $request->input("pisoId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "pisoId" => "required",
      "codMedico" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $habitacion = CamasHabitaciones::where("PISO_ID", $pisoId)->get();

      if (count($habitacion) > 0) {
        return CustomResponse::failure(
          "No se puede eliminar el piso, tiene habitaciones asociadas"
        );
      }

      $model = CamasPisos::query()
        ->where(["PISO_ID" => $pisoId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0]
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Eliminar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Piso",
      ]);

      $piso = CamasPisos::query()
        ->where(["PISO_ID" => $pisoId])
        ->delete();

      return CustomResponse::success("piso eliminado", $piso);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function createPiso(Request $request)
  {
    $nombre = $request->input("nombre");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "nombre" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $idPiso = round(microtime(true) * 1000) . "OH" . uniqid();

      $detalles = [
        "codMedico" => $codMed,
        "New" => json_encode([
          "PISO_ID" => $idPiso,
          "NOMBRE_PISO" => $nombre,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Agregar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Piso",
      ]);

      CamasPisos::insert([
        "PISO_ID" => $idPiso,
        "NOMBRE_PISO" => $nombre,
      ]);
      return CustomResponse::success("piso creado");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function editPiso(Request $request)
  {
    $nombre = $request->input("nombre");
    $pisoId = $request->input("pisoId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "nombre" => "required",
      "pisoId" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $model = CamasPisos::query()
        ->where(["PISO_ID" => $pisoId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "PISO_ID" => $pisoId,
          "NOMBRE_PISO" => $nombre,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Editar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Piso",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS_PISOS SET NOMBRE_PISO = ? WHERE PISO_ID = ?",
        [$nombre, $pisoId]
      );
      return CustomResponse::success("piso editado");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  //** ------------------------- Camas  -------------------------*/

  function getCamas(Request $request)
  {
    try {
      $camas = Camas::select("*")
        ->leftJoin(
          "CME_PACIENTE",
          "HCW_CAMAS.PACIENTE",
          "=",
          "CME_PACIENTE.COD_PACIENTE"
        )
        ->join(
          "HCW_CAMAS_HABITACIONES",
          "HCW_CAMAS.HABITACION_ID",
          "=",
          "HCW_CAMAS_HABITACIONES.HABITACION_ID"
        )
        ->join(
          "HCW_CAMAS_PISOS",
          "HCW_CAMAS_HABITACIONES.PISO_ID",
          "=",
          "HCW_CAMAS_PISOS.PISO_ID"
        )
        ->orderBy("NOMBRE_PISO")
        ->orderBy("NOMBRE_HABITACION")
        ->orderBy("NUMERO")
        ->get();
      return CustomResponse::success("camas", $camas);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function deleteCama(Request $request)
  {
    $camaId = $request->input("camaId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "codMedico" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0]
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Eliminar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      $cama = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->delete();
      return CustomResponse::success("cama eliminada", $cama);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function createCama(Request $request)
  {
    $numero = $request->input("numero");
    $habitacionId = $request->input("habitacionId");
    $tipo = $request->input("tipo");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "numero" => "required",
      "habitacionId" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $idCama = round(microtime(true) * 1000) . "OH" . uniqid();

      $detalles = [
        "codMedico" => $codMed,
        "New" => json_encode([
          "CAMA_ID" => $idCama,
          "NUMERO" => $numero,
          "HABITACION_ID" => $habitacionId,
          "ESTADO" => "0",
          "TIPO" => $tipo,
          "TRANSFERIDO" => "0",
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Agregar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      Camas::insert([
        "CAMA_ID" => $idCama,
        "NUMERO" => $numero,
        "HABITACION_ID" => $habitacionId,
        "ESTADO" => "0",
        "TIPO" => $tipo,
        "TRANSFERIDO" => "0",
      ]);
      return CustomResponse::success("cama creado");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function editCama(Request $request)
  {
    $numero = $request->input("numero");
    $camaId = $request->input("camaId");
    $tipo = $request->input("tipo");
    $habitacionId = $request->input("habitacionId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "numero" => "required",
      "camaId" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "NUMERO" => $numero,
          "HABITACION_ID" => $habitacionId,
          "TIPO" => $tipo,
          "CAMA_ID" => $camaId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Editar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS SET NUMERO = ? , HABITACION_ID = ?, TIPO = ? WHERE CAMA_ID = ?",
        [$numero, $habitacionId, $tipo, $camaId]
      );
      return CustomResponse::success("cama editada");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function transferirCama(Request $request)
  {
    $camaId = $request->input("camaId");
    $tipo = $request->input("tipo");
    $habitacionId_anterior = $request->input("habitacionId_anterior");
    $habitacionId_nuevo = $request->input("habitacionId");
    $habitacion_anterior = $request->input("habitacion_anterior");
    $piso_anterior = $request->input("piso_anterior");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "tipo" => "required",
      "habitacionId_anterior" => "required",
      "habitacionId" => "required",
      "habitacion_anterior" => "required",
      "piso_anterior" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $fecha = new DateTime();

      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "HABITACION_ID_ANTERIOR" => $habitacionId_anterior,
          "HABITACION_ID" => $habitacionId_nuevo,
          "TIPO" => $tipo,
          "TRANSFERIDO" => '1',
          "FECHA_TRANSFERIDO" => $fecha,
          "HABITACION_ANTERIOR" => $habitacion_anterior,
          "PISO_ANTERIOR" => $piso_anterior,
          "CAMA_ID" => $camaId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Transferir",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS SET HABITACION_ID_ANTERIOR = ? , HABITACION_ID = ?, TIPO = ?, TRANSFERIDO = ?, FECHA_TRANSFERIDO = ?, HABITACION_ANTERIOR = ?, PISO_ANTERIOR = ? WHERE CAMA_ID = ?",
        [$habitacionId_anterior, $habitacionId_nuevo, $tipo, '1', $fecha, $habitacion_anterior, $piso_anterior, $camaId,]
      );
      return CustomResponse::success("cama editada");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function devolverCama(Request $request)
  {
    $camaId = $request->input("camaId");
    $habitacionId_anterior = $request->input("habitacionId_anterior");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "habitacionId_anterior" => "required"
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $fecha = new DateTime();

      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "HABITACION_ID_ANTERIOR" => null,
          "HABITACION_ID" => $habitacionId_anterior,
          "TRANSFERIDO" => '0',
          "FECHA_TRANSFERIDO" => $fecha,
          "HABITACION_ANTERIOR" => null,
          "PISO_ANTERIOR" => null,
          "CAMA_ID" => $camaId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Devolver",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS SET HABITACION_ID_ANTERIOR = NULL , HABITACION_ID = ?, TRANSFERIDO = ?, FECHA_TRANSFERIDO = ?, HABITACION_ANTERIOR = NULL, PISO_ANTERIOR = NULL WHERE CAMA_ID = ?",
        [$habitacionId_anterior, '0', $fecha, $camaId]
      );
      return CustomResponse::success("Cama devuelta");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function changeEstado(Request $request)
  {
    $camaId = $request->input("camaId");
    $estado = $request->input("estado");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "estado" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {

      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "ESTADO" => $estado,
          "CAMA_ID" => $camaId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Mantenimiento",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update("UPDATE HCW_CAMAS SET ESTADO = ? WHERE CAMA_ID = ?", [
        $estado,
        $camaId,
      ]);
      return CustomResponse::success("estado asignado");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function asignacionCama(Request $request)
  {
    $camaId = $request->input("camaId");
    $codPaciente = $request->input("codPaciente");
    $especialidad = $request->input("especialidad");
    // $dias = $request->input("dias");
    $genero = $request->input("genero");
    $codMed = $request->input('codMedico');
    $id = $request->input('idHospitalizacion');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "idHospitalizacion" => "required",
      "codPaciente" => "required",
      "especialidad" => "required",
      // "dias" => "required",
      "idHospitalizacion" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    $estado = 1;
    $fechaIngreso = new DateTime();

    try {
      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "PACIENTE" => $codPaciente,
          "ESPECIALIDAD" => $especialidad,
          "ESTADO" => $estado,
          "FECHA_INGRESO" => $fechaIngreso,
          // "DIAS" => $dias,
          "GENERO" => $genero,
          "CAMA_ID" => $camaId,
          "HISTORIA_CLINICA" => $id,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Asignar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS SET PACIENTE = ?, ESPECIALIDAD = ?, ESTADO = ?, FECHA_INGRESO = ?, GENERO = ?, HISTORIA_CLINICA = ? WHERE CAMA_ID = ?",
        [
          $codPaciente,
          $especialidad,
          $estado,
          $fechaIngreso,
          // $dias,
          $genero,
          $id,
          $camaId,
        ]
      );
      DB::update("update HCW_HOSPITALIZACION set ASIGNADO = ? where HISTORIA_CLINICA = ?", ["1", $id]);
      return CustomResponse::success("cama asignada");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function liberarCama(Request $request)
  {
    $camaId = $request->input("camaId");
    $codMed = $request->input('codMedico');
    $id = $request->input('idHospitalizacion');
    $motivo = $request->input('motivoBaja');

    $validator = Validator::make($request->all(), [
      "camaId" => "required",
      "codMedico" => "required",
      "idHospitalizacion" => "required",
      "motivoBaja" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    $estado = 0;

    try {

      $model = Camas::query()
        ->where(["CAMA_ID" => $camaId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "PACIENTE" => null,
          "ESPECIALIDAD" => null,
          "ESTADO" => $estado,
          "FECHA_INGRESO" => null,
          "GENERO" => null,
          "CAMA_ID" => $camaId,
          "HISTORIA_CLINICA" => $id,
          "MOTIVO_BAJA" => $motivo,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Liberar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Cama",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS SET PACIENTE = NULL, ESPECIALIDAD = NULL, ESTADO = ?, FECHA_INGRESO = NULL, GENERO = NULL, HISTORIA_CLINICA = NULL WHERE CAMA_ID = ?",
        [$estado, $camaId]
      );
      DB::update("update HCW_HOSPITALIZACION set ASIGNADO = ?, MOTIVO_BAJA = ? where HISTORIA_CLINICA = ?", ["0", $motivo, $id]);
      return CustomResponse::success("cama liberada");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  //** ------------------------- Habitaciones ------------------------- */

  function getHabitaciones(Request $request)
  {
    try {
      $pisos = CamasHabitaciones::select("*")
        ->join(
          "HCW_CAMAS_PISOS",
          "HCW_CAMAS_HABITACIONES.PISO_ID",
          "=",
          "HCW_CAMAS_PISOS.PISO_ID"
        )
        ->orderBy("NOMBRE_PISO")
        ->orderBy("NOMBRE_HABITACION")
        ->get();
      return CustomResponse::success("habitaciones", $pisos);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function deleteHabitacion(Request $request)
  {
    $habitacionId = $request->input("habitacionId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "habitacionId" => "required",
      "codMedico" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $camas = Camas::where("HABITACION_ID", $habitacionId)->get();

      if (count($camas) > 0) {
        return CustomResponse::failure(
          "No se puede eliminar la habitacion porque tiene camas asignadas"
        );
      }

      $model = CamasHabitaciones::query()
        ->where(["HABITACION_ID" => $habitacionId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0]
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Eliminar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Habitación",
      ]);

      $habitacion = CamasHabitaciones::query()
        ->where(["HABITACION_ID" => $habitacionId])
        ->delete();
      return CustomResponse::success("habitacion eliminada", $habitacion);
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function createHabitacion(Request $request)
  {
    $nombre = $request->input("nombre");
    $pisoId = $request->input("pisoId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "nombre" => "required",
      "pisoId" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $idHabitacion = round(microtime(true) * 1000) . "OH" . uniqid();

      $detalles = [
        "codMedico" => $codMed,
        "New" => json_encode([
          "HABITACION_ID" => $idHabitacion,
          "NOMBRE_HABITACION" => $nombre,
          "PISO_ID" => $pisoId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Agregar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Habitación",
      ]);

      CamasHabitaciones::insert([
        "HABITACION_ID" => $idHabitacion,
        "NOMBRE_HABITACION" => $nombre,
        "PISO_ID" => $pisoId,
      ]);
      return CustomResponse::success("habitacion creada");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  function editHabitacion(Request $request)
  {
    $nombre = $request->input("nombre");
    $pisoId = $request->input("pisoId");
    $habitacionId = $request->input("habitacionId");
    $codMed = $request->input('codMedico');

    $validator = Validator::make($request->all(), [
      "nombre" => "required",
      "pisoId" => "required",
      "habitacionId" => "required",
    ]);

    if ($validator->fails()) {
      return CustomResponse::failure("Datos faltantes");
    }

    try {
      $model = CamasHabitaciones::query()
        ->where(["HABITACION_ID" => $habitacionId])
        ->get();

      $detalles = [
        "codMedico" => $codMed,
        "Old" => $model[0],
        "New" => json_encode([
          "NOMBRE_HABITACION" => $nombre,
          "PISO_ID" => $pisoId,
          "HABITACION_ID" => $habitacionId,
        ])
      ];

      CamasLog::insert([
        "ID_LOG_CAMAS" => round(((microtime(true)) * 1000)) . 'OH' . uniqid(),
        "TIPO" => "Editar",
        "FECHA" => date("Y-m-d H:i:s"),
        "DETALLES" => json_encode($detalles),
        "COD_MEDICO" => $codMed,
        "MODELO" => "Habitación",
      ]);

      DB::update(
        "UPDATE HCW_CAMAS_HABITACIONES SET NOMBRE_HABITACION = ?, PISO_ID = ? WHERE HABITACION_ID = ?",
        [$nombre, $pisoId, $habitacionId]
      );
      return CustomResponse::success("habitacion editado");
    } catch (\Throwable $th) {
      return CustomResponse::failure($th->getMessage());
    }
  }

  //** ------------------------- OTROS ------------------------- */

  function getPacientes(Request $request)
  {
    $codPaciente = $request->input("codPaciente");
    $nombre = $request->input("nombre");

    $pacientes = null;

    if ($codPaciente != null) {
      $pacientes = DB::select(
        DB::raw(
          "SELECT * FROM (
              select * FROM (
                SELECT 
                p.COD_PACIENTE, 
                p.NOM_CLI, 
                p.APE_MAT_CLI, 
                p.APE_PAT_CLI, 
                p.NUM_DOCUMENTO, 
                p.SEXO_CLI, 
                h.ASIGNADO,
                h.HISTORIA_CLINICA,
                h.MOTIVO_BAJA,
                c.NUMERO AS CAMA,
                d.NOMBRE_HABITACION AS HABITACION,
                e.NOMBRE_PISO AS PISO,
                a.COD_MEDICO,
                CONCAT(NOM_CLI,CONCAT(' ',CONCAT(APE_PAT_CLI,CONCAT(' ', APE_MAT_CLI)))) AS NOMBRE_COMPLETO
                from CME_PACIENTE p
                right join HCW_HOSPITALIZACION h
                on p.COD_PACIENTE = h.COD_PACIENTE
                full join HCW_CAMAS c
                on c.PACIENTE = h.COD_PACIENTE
                full join HCW_CAMAS_HABITACIONES d
                on d.HABITACION_ID = c.HABITACION_ID
                full join HCW_CAMAS_PISOS e
                on e.PISO_ID = d.PISO_ID
                full join CME_ATENCION_MEDICA a
                on a.NUM_ATEN_MED = h.HISTORIA_CLINICA
                ) 
              WHERE HISTORIA_CLINICA LIKE '%" . $codPaciente . "%') 
            WHERE ROWNUM <=20"
        )
      );
    }

    if ($nombre != null) {
      $pacientes = DB::select(
        DB::raw(
          "SELECT * FROM (
            select * FROM (
              SELECT 
              p.COD_PACIENTE, 
              p.NOM_CLI, 
              p.APE_MAT_CLI, 
              p.APE_PAT_CLI, 
              p.NUM_DOCUMENTO, 
              p.SEXO_CLI, 
              h.ASIGNADO,
              h.HISTORIA_CLINICA,
              h.MOTIVO_BAJA,
              c.NUMERO AS CAMA,
              d.NOMBRE_HABITACION AS HABITACION,
              e.NOMBRE_PISO AS PISO,
              a.COD_MEDICO,
              m.DES_NOM_MEDICO,
              m.DES_APE_MEDICO,
              CONCAT(NOM_CLI,CONCAT(' ',CONCAT(APE_PAT_CLI,CONCAT(' ', APE_MAT_CLI)))) AS NOMBRE_COMPLETO
              from CME_PACIENTE p
              right join HCW_HOSPITALIZACION h
              on p.COD_PACIENTE = h.COD_PACIENTE
              full join HCW_CAMAS c
              on c.PACIENTE = h.COD_PACIENTE
              full join HCW_CAMAS_HABITACIONES d
              on d.HABITACION_ID = c.HABITACION_ID
              full join HCW_CAMAS_PISOS e
              on e.PISO_ID = d.PISO_ID
              full join CME_ATENCION_MEDICA a
              on a.NUM_ATEN_MED = h.HISTORIA_CLINICA
              full join MAE_MEDICO m
              on a.COD_MEDICO = m.COD_MEDICO
              ) 
            WHERE lower(NOMBRE_COMPLETO) LIKE '%" . strtolower($nombre) . "%') 
          WHERE ROWNUM <=20"
        )
      );
    }
    // $filtroAlta = [];
    // foreach ($pacientes as $key => $row) {
    //   if (!$row['MOTIVO_BAJA']) {
    //     array_push($filtroAlta, $pacientes[$key]);
    //   }
    // }
    return CustomResponse::success("pacientes", $pacientes);
  }

  function getEspecilidades(Request $request)
  {
    $especialidades = DB::select("select * from cc_consultorio");
    return CustomResponse::success("especialidades", $especialidades);
  }

  function getMotivos(Request $request)
  {
    $model = DB::select("SELECT * FROM HCW_MOTIVO_BAJA");
    return CustomResponse::success("Motivos", $model);
  }
}
