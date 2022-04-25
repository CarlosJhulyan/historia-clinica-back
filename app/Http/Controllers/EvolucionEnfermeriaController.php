<?php

namespace App\Http\Controllers;

use App\Core\CustomResponse;
use App\Models\EvolucionEnfermeria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class EvolucionEnfermeriaController extends Controller
{
    public function agregarEvolucionEnfermeria(Request $request) {
        $cod_paciente = $request->input('COD_PACIENTE');
        $cod_medico = $request->input('COD_MEDICO');
        $nom_paciente = $request->input('NOM_PACIENTE');
        $nom_medico = $request->input('NOM_MEDICO');
        $narracion_estado = $request->input('NARRACION_ESTADO');
        $nro_hc = $request->input('NRO_HC');
        $turno = $request->input('TURNO');

        $validator = Validator::make($request->all(), [
            'NRO_HC' => 'required',
            'COD_PACIENTE' => 'required',
            'COD_MEDICO' => 'required',
            'NARRACION_ESTADO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('Datos faltantes.');
        } 

        try {
            EvolucionEnfermeria::insert([
                'ID' => round(((microtime(true)) * 1000)) . 'EE' . uniqid(),
                'COD_PACIENTE' => $cod_paciente,
                'COD_MEDICO' => $cod_medico,
                'NOM_PACIENTE' => $nom_paciente,
                'NOM_MEDICO' => $nom_medico,
                'NARRACION_ESTADO' => $narracion_estado,
                'NRO_HC' => $nro_hc,
                'TURNO' => $turno,
            ]);
            return CustomResponse::success('Se registró correctamente.');
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (str_contains($th->getMessage(), 'large for column')) {
                return CustomResponse::failure('La descripción del estado del paciente admité un máximo de 300 caracteres.');
            }
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function filtrarEEPorFecha(Request $request) {
        $fechaInicio = $request->input('FECHA_INICIO');
        $fechaFin = $request->input('FECHA_FIN');
        $nomPaciente = $request->input('NOM_PACIENTE');
        $nomMedico = $request->input('NOM_MEDICO');

        $validator = Validator::make($request->all(), [
            'FECHA_INICIO' => 'required',
            'FECHA_FIN' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se ingreso un rango de fechas.');
        }

        try {
            $data = [];
            
            if ($nomMedico) {
                if ($nomPaciente) {
                    $data = EvolucionEnfermeria::select('*')
                        ->where('NOM_MEDICO', $nomMedico)
                        ->where('NOM_PACIENTE', $nomPaciente)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                } else {
                    $data = EvolucionEnfermeria::select('*')
                        ->where('NOM_MEDICO', $nomMedico)
                        ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                        ->orderBy('FECHA', 'DESC')
                        ->get();
                }
            } else if ($nomPaciente) {
                $data = EvolucionEnfermeria::select('*')
                    ->where('NOM_PACIENTE', $nomPaciente)
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            } else {
                $data = EvolucionEnfermeria::select('*')
                    ->whereBetween('FECHA', [$fechaInicio, $fechaFin])
                    ->orderBy('FECHA', 'DESC')
                    ->get();
            }

            if (count($data) <= 0) {
                return CustomResponse::success('No se encontraron registros.', $data);
            }
            return CustomResponse::success(count($data) . ' registros encontrados.', $data);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function getPacientes(Request $request) {
        $nomPaciente = $request->input('NOM_PACIENTE');

        $validator = Validator::make($request->all(), [
            'NOM_PACIENTE' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se esta ingresando el nombre del paciente.');
        }

        try {
            $data = EvolucionEnfermeria::select('NOM_PACIENTE', 'ID as KEY', 'NOM_PACIENTE as VALUE')
                ->where('NOM_PACIENTE', 'like', '%'. strtoupper($nomPaciente) .'%')
                ->get();
            
            return CustomResponse::success('Pacientes encontrados.', $data);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores.');
        }
    }

    public function getMedicos(Request $request) {
        $nomMedico = $request->input('NOM_MEDICO');

        $validator = Validator::make($request->all(), [
            'NOM_MEDICO' => 'required'
        ]);

        if ($validator->fails()) {
            return CustomResponse::failure('No se esta ingresando el nombre del médico.');
        }

        try {
            $data = EvolucionEnfermeria::select('NOM_MEDICO', 'ID as KEY', 'NOM_MEDICO as VALUE')
                ->where('NOM_MEDICO', 'like', '%'. strtoupper($nomMedico) .'%')
                ->get();
            
            return CustomResponse::success('Medicos encontrados.', $data);
        } catch (\Throwable $th) {
            return CustomResponse::failure('Error en los servidores.');
        }
    }
}
