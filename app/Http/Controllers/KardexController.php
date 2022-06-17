<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\Estemat;
use App\Models\Invasivos;
use App\Models\Kardex;
use App\Models\KardexEspecial;
use App\Models\KardexExamen;
use App\Models\KardexInterconsulta;
use App\Models\KardexTratamiento;
use App\Models\KardexTratamientoHistorial;
use App\Models\KardexTratamientoHorario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KardexController extends Controller
{
    function setKardexTratamiento(Request $request)
    {
        $codMedico = $request->input('codMedico');
        $nomMedico = $request->input('nomMedico');
        $codPaciente = $request->input('codPaciente');
        $nomPaciente = $request->input('nomPaciente');
        $hc = $request->input('hc');
        $tratamientos = $request->input('tratamiento');
        $accion = $request->input('accion');

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "nomMedico" => "required",
            "codPaciente" => "required",
            "nomPaciente" => "required",
            "hc" => "required",
            "tratamiento" => "required",
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $kardex = Kardex::where(['HC' => $hc])->first();

            if ($kardex) {

                $idKardex = $kardex['id'];

                Kardex::where('ID', $idKardex)
                    ->update([
                        "ID" => $idKardex,
                        "COD_MEDICO" => $codMedico,
                        "NOM_MEDICO" => $nomMedico,
                        "COD_PACIENTE" => $codPaciente,
                        "NOM_PACIENTE" => $nomPaciente,
                        "HC" => $hc
                    ]);

                foreach ($tratamientos as $key => $tratamiento) {

                    $kardexTratamiento = KardexTratamiento::where([
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "VIA_ADMINISTRACION" => $tratamiento['viaAdministracion'],
                    ])->first();

                    if ($kardexTratamiento) {

                        $idTratamiento = $kardexTratamiento['id'];

                        KardexTratamiento::where('ID', $idTratamiento)
                            ->update([
                                "ID" => $idTratamiento,
                                "ID_KARDEX" => $idKardex,
                                "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                                "PRODUCTO" => $tratamiento['producto'],
                                "VIA_ADMINISTRACION" => $tratamiento['viaAdministracion'],
                                "ETIQUETA_VIA" => $tratamiento['etiquetaVia'],
                                "DOSIS" => $tratamiento['dosis'],
                                "CANTIDAD" => $tratamiento['cantidad'],
                                "DURACION" => $tratamiento['duracion'],
                                "FRECUENCIA" => $tratamiento['frecuencia'],
                                "ESTADO" => $tratamiento['estado'],
                            ]);
                        if ($tratamiento['horario']) {
                            foreach ($tratamiento['horario'] as $key => $horario) {
                                $kardexTratamientoHorario = KardexTratamientoHorario::where([
                                    "ID_KARDEX_TRATAMIENTO" => $idTratamiento,
                                    "HORA" => new DateTime($horario['hora'])
                                ])->first();

                                if ($kardexTratamientoHorario) {

                                    $idTratamientoHorario = $kardexTratamientoHorario['id'];

                                    KardexTratamientoHorario::where('ID', $idTratamientoHorario)
                                        ->update([
                                            "ID" => $idTratamientoHorario,
                                            "ID_KARDEX_TRATAMIENTO" => $idTratamiento,
                                            "HORA" => new DateTime($horario['hora']),
                                            "ADMINISTRADO" => $horario['administrado'],
                                        ]);
                                } else {

                                    $idTratamientoHorario = round(microtime(true) * 1000) . "KH" . uniqid();

                                    KardexTratamientoHorario::insert([
                                        "ID" => $idTratamientoHorario,
                                        "ID_KARDEX_TRATAMIENTO" => $idTratamiento,
                                        "HORA" => new DateTime($horario['hora']),
                                        "ADMINISTRADO" => $horario['administrado'],
                                    ]);
                                }
                            }
                        }
                    } else {

                        $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                        KardexTratamiento::insert([
                            "ID" => $idTratamiento,
                            "ID_KARDEX" => $idKardex,
                            "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                            "PRODUCTO" => $tratamiento['producto'],
                            "VIA_ADMINISTRACION" => $tratamiento['viaAdministracion'],
                            "ETIQUETA_VIA" => $tratamiento['etiquetaVia'],
                            "DOSIS" => $tratamiento['dosis'],
                            "CANTIDAD" => $tratamiento['cantidad'],
                            "DURACION" => $tratamiento['duracion'],
                            "FRECUENCIA" => $tratamiento['frecuencia'],
                            "ESTADO" => $tratamiento['estado'],
                        ]);
                        if ($tratamiento['horario']) {
                            foreach ($tratamiento['horario'] as $key => $horario) {

                                $idTratamientoHorario = round(microtime(true) * 1000) . "KH" . uniqid();

                                KardexTratamientoHorario::insert([
                                    "ID" => $idTratamientoHorario,
                                    "ID_KARDEX_TRATAMIENTO" => $idTratamiento,
                                    "HORA" => new DateTime($horario['hora']),
                                    "ADMINISTRADO" => $horario['administrado'],
                                ]);
                            }
                        }
                    }
                }

                $idKardexHistorial = round(microtime(true) * 1000) . "KTH" . uniqid();
                $detalles = json_encode($tratamientos);
                KardexTratamientoHistorial::insert([
                    "ID" => $idKardexHistorial,
                    "ID_KARDEX" => $idKardex,
                    "HC" => $hc,
                    "COD_MED" => $codMedico,
                    "NOM_MED" => $nomMedico,
                    "FECHA" => date("Y-m-d H:i:s"),
                    "ACCION" => $accion,
                    "DETALLES" => $detalles,
                ]);
            } else {

                $idKardex = round(microtime(true) * 1000) . "KX" . uniqid();

                Kardex::insert([
                    "ID" => $idKardex,
                    "COD_MEDICO" => $codMedico,
                    "NOM_MEDICO" => $nomMedico,
                    "COD_PACIENTE" => $codPaciente,
                    "NOM_PACIENTE" => $nomPaciente,
                    "HC" => $hc,
                    "FECHA" => date("Y-m-d"),
                ]);

                foreach ($tratamientos as $key => $tratamiento) {

                    $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                    KardexTratamiento::insert([
                        "ID" => $idTratamiento,
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "PRODUCTO" => $tratamiento['producto'],
                        "VIA_ADMINISTRACION" => $tratamiento['viaAdministracion'],
                        "ETIQUETA_VIA" => $tratamiento['etiquetaVia'],
                        "DOSIS" => $tratamiento['dosis'],
                        "CANTIDAD" => $tratamiento['cantidad'],
                        "DURACION" => $tratamiento['duracion'],
                        "FRECUENCIA" => $tratamiento['frecuencia'],
                        "ESTADO" => $tratamiento['estado'],
                    ]);
                    if ($tratamiento['horario']) {
                        foreach ($tratamiento['horario'] as $key => $horario) {

                            $idTratamientoHorario = round(microtime(true) * 1000) . "KH" . uniqid();

                            KardexTratamientoHorario::insert([
                                "ID" => $idTratamientoHorario,
                                "ID_KARDEX_TRATAMIENTO" => $idTratamiento,
                                "HORA" => new DateTime($horario['hora']),
                                "ADMINISTRADO" => $horario['administrado'],
                            ]);
                        }
                    }
                }

                $idKardexHistorial = round(microtime(true) * 1000) . "KTH" . uniqid();
                $detalles = json_encode($tratamientos);
                KardexTratamientoHistorial::insert([
                    "ID" => $idKardexHistorial,
                    "ID_KARDEX" => $idKardex,
                    "HC" => $hc,
                    "COD_MED" => $codMedico,
                    "NOM_MED" => $nomMedico,
                    "FECHA" => date("Y-m-d H:i:s"),
                    "ACCION" => $accion,
                    "DETALLES" => $detalles,
                ]);
            }
            return CustomResponse::success("Kardex creado");
        } catch (\Throwable $th) {
            error_log($th);
            return CustomResponse::failure($th->getMessage());
        }
    }

    function setKardexExamen(Request $request)
    {
        $codMedico = $request->input('codMedico');
        $nomMedico = $request->input('nomMedico');
        $codPaciente = $request->input('codPaciente');
        $nomPaciente = $request->input('nomPaciente');
        $hc = $request->input('hc');
        $examenes = $request->input('examen');

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "nomMedico" => "required",
            "codPaciente" => "required",
            "nomPaciente" => "required",
            "hc" => "required",
            "examen" => "required",
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $kardex = Kardex::where(['HC' => $hc])->first();

            if ($kardex) {

                $idKardex = $kardex['id'];

                Kardex::where('ID', $idKardex)
                    ->update([
                        "ID" => $idKardex,
                        "COD_MEDICO" => $codMedico,
                        "NOM_MEDICO" => $nomMedico,
                        "COD_PACIENTE" => $codPaciente,
                        "NOM_PACIENTE" => $nomPaciente,
                        "HC" => $hc
                    ]);

                foreach ($examenes as $key => $tratamiento) {

                    $kardexExamenes = KardexExamen::where([
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "TIPO" => $tratamiento['tipo'],
                    ])->first();

                    if ($kardexExamenes) {

                        $idTratamiento = $kardexExamenes['id'];

                        KardexExamen::where('ID', $idTratamiento)
                            ->update([
                                "ID" => $idTratamiento,
                                "ID_KARDEX" => $idKardex,
                                "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                                "PRODUCTO" => $tratamiento['producto'],
                                "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                                "RUC" => $tratamiento['ruc'],
                                "TIPO" => $tratamiento['tipo'],
                                "ESTADO" => $tratamiento['estado'],
                                "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                                "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                            ]);
                    } else {

                        $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                        KardexExamen::insert([
                            "ID" => $idTratamiento,
                            "ID_KARDEX" => $idKardex,
                            "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                            "PRODUCTO" => $tratamiento['producto'],
                            "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                            "RUC" => $tratamiento['ruc'],
                            "TIPO" => $tratamiento['tipo'],
                            "ESTADO" => $tratamiento['estado'],
                            "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                            "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                        ]);
                    }
                }
            } else {

                $idKardex = round(microtime(true) * 1000) . "KX" . uniqid();

                Kardex::insert([
                    "ID" => $idKardex,
                    "COD_MEDICO" => $codMedico,
                    "NOM_MEDICO" => $nomMedico,
                    "COD_PACIENTE" => $codPaciente,
                    "NOM_PACIENTE" => $nomPaciente,
                    "HC" => $hc,
                    "FECHA" => date("Y-m-d"),
                ]);

                foreach ($examenes as $key => $tratamiento) {

                    $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                    KardexExamen::insert([
                        "ID" => $idTratamiento,
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "PRODUCTO" => $tratamiento['producto'],
                        "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                        "RUC" => $tratamiento['ruc'],
                        "TIPO" => $tratamiento['tipo'],
                        "ESTADO" => $tratamiento['estado'],
                        "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                        "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                    ]);
                }
            }
            return CustomResponse::success("Kardex creado");
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function setKardexInterconsulta(Request $request)
    {
        $codMedico = $request->input('codMedico');
        $nomMedico = $request->input('nomMedico');
        $codPaciente = $request->input('codPaciente');
        $nomPaciente = $request->input('nomPaciente');
        $hc = $request->input('hc');
        $interconsultas = $request->input('interconsulta');

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "nomMedico" => "required",
            "codPaciente" => "required",
            "nomPaciente" => "required",
            "hc" => "required",
            "interconsulta" => "required",
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $kardex = Kardex::where(['HC' => $hc])->first();

            if ($kardex) {

                $idKardex = $kardex['id'];

                Kardex::where('ID', $idKardex)
                    ->update([
                        "ID" => $idKardex,
                        "COD_MEDICO" => $codMedico,
                        "NOM_MEDICO" => $nomMedico,
                        "COD_PACIENTE" => $codPaciente,
                        "NOM_PACIENTE" => $nomPaciente,
                        "HC" => $hc
                    ]);

                foreach ($interconsultas as $key => $tratamiento) {

                    $kardexExamenes = KardexInterconsulta::where([
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "TIPO" => $tratamiento['tipo'],
                    ])->first();

                    if ($kardexExamenes) {

                        $idTratamiento = $kardexExamenes['id'];

                        KardexInterconsulta::where('ID', $idTratamiento)
                            ->update([
                                "ID" => $idTratamiento,
                                "ID_KARDEX" => $idKardex,
                                "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                                "PRODUCTO" => $tratamiento['producto'],
                                "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                                "RUC" => $tratamiento['ruc'],
                                "TIPO" => $tratamiento['tipo'],
                                "ESTADO" => $tratamiento['estado'],
                                "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                                "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                            ]);
                    } else {

                        $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                        KardexInterconsulta::insert([
                            "ID" => $idTratamiento,
                            "ID_KARDEX" => $idKardex,
                            "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                            "PRODUCTO" => $tratamiento['producto'],
                            "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                            "RUC" => $tratamiento['ruc'],
                            "TIPO" => $tratamiento['tipo'],
                            "ESTADO" => $tratamiento['estado'],
                            "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                            "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                        ]);
                    }
                }
            } else {

                $idKardex = round(microtime(true) * 1000) . "KX" . uniqid();

                Kardex::insert([
                    "ID" => $idKardex,
                    "COD_MEDICO" => $codMedico,
                    "NOM_MEDICO" => $nomMedico,
                    "COD_PACIENTE" => $codPaciente,
                    "NOM_PACIENTE" => $nomPaciente,
                    "HC" => $hc,
                    "FECHA" => date("Y-m-d"),
                ]);

                foreach ($interconsultas as $key => $tratamiento) {

                    $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                    KardexInterconsulta::insert([
                        "ID" => $idTratamiento,
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "PRODUCTO" => $tratamiento['producto'],
                        "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                        "RUC" => $tratamiento['ruc'],
                        "TIPO" => $tratamiento['tipo'],
                        "ESTADO" => $tratamiento['estado'],
                        "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                        "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                    ]);
                }
            }
            return CustomResponse::success("Kardex creado");
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function setKardexEspecial(Request $request)
    {
        $codMedico = $request->input('codMedico');
        $nomMedico = $request->input('nomMedico');
        $codPaciente = $request->input('codPaciente');
        $nomPaciente = $request->input('nomPaciente');
        $hc = $request->input('hc');
        $especiales = $request->input('especial');

        $validator = Validator::make($request->all(), [
            "codMedico" => "required",
            "nomMedico" => "required",
            "codPaciente" => "required",
            "nomPaciente" => "required",
            "hc" => "required",
            "especial" => "required",
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure("Datos faltantes");
        }

        try {
            $kardex = Kardex::where(['HC' => $hc])->first();

            if ($kardex) {

                $idKardex = $kardex['id'];

                Kardex::where('ID', $idKardex)
                    ->update([
                        "ID" => $idKardex,
                        "COD_MEDICO" => $codMedico,
                        "NOM_MEDICO" => $nomMedico,
                        "COD_PACIENTE" => $codPaciente,
                        "NOM_PACIENTE" => $nomPaciente,
                        "HC" => $hc
                    ]);

                foreach ($especiales as $key => $tratamiento) {

                    $kardexExamenes = KardexEspecial::where([
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "TIPO" => $tratamiento['tipo'],
                    ])->first();

                    if ($kardexExamenes) {

                        $idTratamiento = $kardexExamenes['id'];

                        KardexEspecial::where('ID', $idTratamiento)
                            ->update([
                                "ID" => $idTratamiento,
                                "ID_KARDEX" => $idKardex,
                                "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                                "PRODUCTO" => $tratamiento['producto'],
                                "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                                "RUC" => $tratamiento['ruc'],
                                "TIPO" => $tratamiento['tipo'],
                                "ESTADO" => $tratamiento['estado'],
                                "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                                "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                            ]);
                    } else {

                        $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                        KardexEspecial::insert([
                            "ID" => $idTratamiento,
                            "ID_KARDEX" => $idKardex,
                            "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                            "PRODUCTO" => $tratamiento['producto'],
                            "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                            "RUC" => $tratamiento['ruc'],
                            "TIPO" => $tratamiento['tipo'],
                            "ESTADO" => $tratamiento['estado'],
                            "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                            "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                        ]);
                    }
                }
            } else {

                $idKardex = round(microtime(true) * 1000) . "KX" . uniqid();

                Kardex::insert([
                    "ID" => $idKardex,
                    "COD_MEDICO" => $codMedico,
                    "NOM_MEDICO" => $nomMedico,
                    "COD_PACIENTE" => $codPaciente,
                    "NOM_PACIENTE" => $nomPaciente,
                    "HC" => $hc,
                    "FECHA" => date("Y-m-d"),
                ]);

                foreach ($especiales as $key => $tratamiento) {

                    $idTratamiento = round(microtime(true) * 1000) . "KT" . uniqid();

                    KardexEspecial::insert([
                        "ID" => $idTratamiento,
                        "ID_KARDEX" => $idKardex,
                        "CODIGO_PRODUCTO" => $tratamiento['codProducto'],
                        "PRODUCTO" => $tratamiento['producto'],
                        "NOMBRE_LABORATORIO" => $tratamiento['nomLaboratorio'],
                        "RUC" => $tratamiento['ruc'],
                        "TIPO" => $tratamiento['tipo'],
                        "ESTADO" => $tratamiento['estado'],
                        "FECHA_TOMA" => $tratamiento['fechaToma'] != 'null' ? new DateTime($tratamiento['fechaToma']) : null,
                        "FECHA_ENTREGA" => $tratamiento['fechaEntrega'] != 'null' ? new DateTime($tratamiento['fechaEntrega']) : null,
                    ]);
                }
            }
            return CustomResponse::success("Kardex creado");
        } catch (\Throwable $th) {
            return CustomResponse::failure($th->getMessage());
        }
    }

    function getKardex(Request $request)
    {
        $historia_clinica = $request->input('hc');

        $validator = Validator::make($request->all(), [
            'hc' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $kardex = Kardex::where(['HC' => $historia_clinica])->first();
                if ($kardex) {
                    $kardex->tratamientos = KardexTratamiento::where(['ID_KARDEX' => $kardex['id']])->get();
                    foreach ($kardex['tratamientos'] as $key => $item) {
                        $item->horarios = KardexTratamientoHorario::where(['ID_KARDEX_TRATAMIENTO' => $item['id']])->get();
                    }
                    $kardex->examenes = KardexExamen::where(['ID_KARDEX' => $kardex['id']])->get();
                    $kardex->interconsultas = KardexInterconsulta::where(['ID_KARDEX' => $kardex['id']])->get();
                    $kardex->especiales = KardexEspecial::where(['ID_KARDEX' => $kardex['id']])->get();
                    return CustomResponse::success('Se encontraron datos', $kardex);
                } else {
                    return CustomResponse::failure('No se encontró el kardex');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    function getKardexHistorialTratamiento(Request $request)
    {
        $historia_clinica = $request->input('hc');

        $validator = Validator::make($request->all(), [
            'hc' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $kardex = KardexTratamientoHistorial::where(['HC' => $historia_clinica])->get();
                if ($kardex) {
                    // $kardex->detalles = json_decode($kardex['detalles']);
                    return CustomResponse::success('Se encontraron datos', $kardex);
                } else {
                    return CustomResponse::failure('No se encontró el kardex');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    function getFechaAtencion(Request $request)
    {
        $COD_PACIENTE = $request->input('codPaciente');
        $COD_GRUPO_CIA = $request->input('codGrupoCia');
        $NRO_ATENCION = $request->input('nroAtencion');

        $validator = Validator::make($request->all(), [
            'codPaciente' => 'required',
            'codGrupoCia' => 'required',
            'nroAtencion' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $kardex = Estemat::where(['COD_PACIENTE' => $COD_PACIENTE, 'COD_GRUPO_CIA' => $COD_GRUPO_CIA, 'NRO_ATENCION' => $NRO_ATENCION])->orderBy('FECHA', 'DESC')->first();
                if ($kardex) {
                    // $kardex->detalles = json_decode($kardex['detalles']);
                    return CustomResponse::success('Se encontraron datos', $kardex);
                } else {
                    return CustomResponse::failure('No se encontró la fecha');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

    
    function addInvasivos(Request $request)
    {
        $FECHA_CVC = $request->input('cvc');
        $FECHA_TET = $request->input('tet');
        $FECHA_VIA_PERIFERIA = $request->input('via_periferica');
        $MOTIVO_VIA_PERIFERICA = $request->input('MOTIVO_VIA_PERIFERICA');
        $FECHA_SNG = $request->input('sng');
        $FECHA_FOLEY = $request->input('foley');
        $codPaciente = $request->input('codPaciente');
        $MOTIVO_CVC = $request->input('MOTIVO_CVC');
        $MOTIVO_TET = $request->input('MOTIVO_TET');
        $MOTIVO_FOLEY = $request->input('MOTIVO_FOLEY');
        $MOTIVO_SNG = $request->input('MOTIVO_SNG');


        $validator = Validator::make($request->all(), [
            
            'cvc' => 'required',
            'tet' => 'required',
            'via_periferica' => 'required',
            'MOTIVO_VIA_PERIFERICA' => 'required',
            'sng' => 'required',
            'foley' => 'required',
            'codPaciente' => 'required',
            'MOTIVO_CVC' => 'required',
            'MOTIVO_TET' => 'required',
            'MOTIVO_FOLEY' => 'required',
            'MOTIVO_SNG' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                Invasivos::insert([
                    "FECHA_CVC" => $FECHA_CVC,
                    "FECHA_TET" => $FECHA_TET,
                    "FECHA_VIA_PERIFERIA" => $FECHA_VIA_PERIFERIA,
                    "MOTIVO_VIA_PERIFERICA" => $MOTIVO_VIA_PERIFERICA,
                    "FECHA_SNG" => $FECHA_SNG,
                    "FECHA_FOLEY" => $FECHA_FOLEY,
                    "codPaciente" =>$codPaciente,
                    'MOTIVO_CVC' => $MOTIVO_CVC,
                    'MOTIVO_TET' => $MOTIVO_TET,
                    'MOTIVO_FOLEY' => $MOTIVO_FOLEY,
                    'MOTIVO_SNG' => $MOTIVO_SNG,
                ]);
                // if ($kardex) {
                    // $kardex->detalles = json_decode($kardex['detalles']);
                    return CustomResponse::success('Se registraron invasivos');
                // } else {
                //     return CustomResponse::failure('No se encontró la fecha');
                // }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }
    function getInvasivos(Request $request)
    {
    
        $codPaciente = $request->input('codPaciente');
        $fecha1cvc = $request->input('fecha1cvc');
        $fecha2cvc = $request->input('fecha2cvc');
        $fecha1tet = $request->input('fecha1tet');
        $fecha2tet = $request->input('fecha2tet');
        $fecha1sng = $request->input('fecha1sng');
        $fecha2sng = $request->input('fecha2sng');
        $fecha1f = $request->input('fecha1f');
        $fecha2f = $request->input('fecha2f');
        $fecha1via = $request->input('fecha1via');
        $fecha2via = $request->input('fecha2via');

        $validator = Validator::make($request->all(), [
            
            'codPaciente' => 'required',
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes');
        } else {
            try {
                $Invasivos=Invasivos::where(['codpaciente' => $codPaciente])
                ->FechaCVC($fecha1cvc,$fecha2cvc)
                ->FechaTET($fecha1tet,$fecha2tet)
                ->FechaSNG($fecha1sng,$fecha2sng)
                ->FechaFOLEY($fecha1f,$fecha2f)
                ->FechaVIA($fecha1via,$fecha2via)
                ->get();
                if ($Invasivos) {
                    return CustomResponse::success('Se encontraron datos ',$Invasivos);
                } else {
                    return CustomResponse::failure('No se encontró la fecha');
                }
            } catch (\Throwable $th) {
                return CustomResponse::failure($th->getMessage());
            }
        }
    }

}
