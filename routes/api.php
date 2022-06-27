<?php

use Illuminate\Support\Facades\Route;
//AUTH
Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/getCMP', 'App\Http\Controllers\AuthController@getCMP');
Route::post('/login-admin', 'App\Http\Controllers\AuthController@loginAdministrador');
Route::post('/login/getUsuario', 'App\Http\Controllers\AuthController@getUsuarioInfoToToken');

//PACIENTES
Route::post('/pacientes', 'App\Http\Controllers\PacientesController@listaEspera');
Route::post('/pacientes/getPaciente', 'App\Http\Controllers\PacientesController@obtenerPaciente');
Route::post('/pacientes/getTipoAcomp', 'App\Http\Controllers\PacientesController@obtenerTipoAcomp');
Route::post('/pacientes/getTipoDoc', 'App\Http\Controllers\PacientesController@obtenerTipoDoc');
Route::post('/pacientes/getEstadoCivil', 'App\Http\Controllers\PacientesController@obtenerEstadoCivil');
Route::post('/pacientes/getInterconsultas', 'App\Http\Controllers\PacientesController@obtenerInterconsultas');
Route::post('/pacientes/getAlergias', 'App\Http\Controllers\PacientesController@obtenerAlergias');
Route::post('/pacientes/setInterconsultas', 'App\Http\Controllers\PacientesController@grabarInterconsultas');
Route::post('/pacientes/setAlergia', 'App\Http\Controllers\PacientesController@grabarAlergia');
Route::post('/pacientes/updateAlergia', 'App\Http\Controllers\PacientesController@editarAlergia');
Route::post('/pacientes/getBusMedico', 'App\Http\Controllers\PacientesController@getBusMedico');
Route::post('/pacientes/getHospitalizacion', 'App\Http\Controllers\PacientesController@obtenerEstadoHospi');
Route::post('/pacientes/setHospitalizacion', 'App\Http\Controllers\PacientesController@grabarHospi');
Route::post('/pacientes/updateHospitalizacion', 'App\Http\Controllers\PacientesController@actualizarHospi');
Route::post('/pacientes/upsertPaciente', 'App\Http\Controllers\PacientesController@upsertPaciente');
Route::post('/pacientes/searchPacientes', 'App\Http\Controllers\PacientesController@searchPacientes');
Route::post('/pacientes/getAtencionPaciente', 'App\Http\Controllers\PacientesController@obtenerAtencionPaciente');
Route::post('/pacientes/getListaEsperaTriaje', 'App\Http\Controllers\PacientesController@listaEsperaTriaje');

//DATA SELECT
Route::get('/combo/maestro', 'App\Http\Controllers\SelectController@maestro');
Route::post('/combo/procedimientos', 'App\Http\Controllers\SelectController@getProcedimientos');
Route::post('/combo/imagenes', 'App\Http\Controllers\SelectController@getImagenes');
Route::post('/combo/laboratorio', 'App\Http\Controllers\SelectController@getLaboratorio');

//DATA TABLA
Route::post('/tabla/procedimientos', 'App\Http\Controllers\DataTablaController@getProcedimientosTabla');
Route::post('/tabla/imagenes', 'App\Http\Controllers\DataTablaController@getImagenesTabla');
Route::post('/tabla/laboratorio', 'App\Http\Controllers\DataTablaController@getLaboratorioTabla');

//DATA OBS
Route::post('/obs/tratamiento', 'App\Http\Controllers\DataObsController@getObsTratamiento');
Route::post('/obs/procedimiento', 'App\Http\Controllers\DataObsController@getObsProcedimiento');
Route::post('/obs/imagenes', 'App\Http\Controllers\DataObsController@getObsImagenes');
Route::post('/obs/laboratorio', 'App\Http\Controllers\DataObsController@getObsLaboratorio');

//TRATAMIENTOS
Route::post('/tratamientos', 'App\Http\Controllers\TratamientoController@tratamientos');
// Route::post('/tratamientos/registrar', 'App\Http\Controllers\TratamientoController@store');
Route::post('/tratamientos/antecedentes', 'App\Http\Controllers\TratamientoController@getAntecedentes');
Route::post('/tratamientos/estomatologico', 'App\Http\Controllers\TratamientoController@getEstomatologico');
Route::post('/tratamientos/recetaSugerido', 'App\Http\Controllers\TratamientoController@sugeridoReceta');
Route::post('/tratamientos/getRecomendaciones', 'App\Http\Controllers\TratamientoController@getRecomendaciones');
Route::post('/tratamientos/setRecomendaciones', 'App\Http\Controllers\TratamientoController@setRecomendaciones');

//ANTECEDENTES
Route::get('/antecedentes/diagnosticos', 'App\Http\Controllers\AntecedentesController@listarDiagnostico');
Route::post('/antecedentes/funcionesvitales', 'App\Http\Controllers\AntecedentesController@funcionesVitales');
Route::post('/antecedentes/hc', 'App\Http\Controllers\AntecedentesController@antecedentesHC');
Route::post('/antecedentes/generales', 'App\Http\Controllers\AntecedentesController@antecedentesGenerales');
Route::post('/antecedentes/getPatologico', 'App\Http\Controllers\AntecedentesController@listarPatologicos');
Route::post('/antecedentes/paneles', 'App\Http\Controllers\AntecedentesController@getPanelesAntecedentes');
Route::post('/antecedentes/setAntecedentes', 'App\Http\Controllers\AntecedentesController@guardarAntecedentes');
Route::post('/antecedentes/setFisiologico', 'App\Http\Controllers\AntecedentesController@guardarFisiologicos');
Route::post('/antecedentes/getHistorial', 'App\Http\Controllers\AntecedentesController@getHistorial');

//ODONTOGRAMA
Route::post('/odontograma/registrar', 'App\Http\Controllers\OdontogramaController@registrar');
Route::post('/odontograma/inicial', 'App\Http\Controllers\OdontogramaController@getOdontogramaInicial');
Route::post('/odontograma/final', 'App\Http\Controllers\OdontogramaController@getOdontogramaFinal');
Route::post('/odontograma/historial', 'App\Http\Controllers\OdontogramaController@getHistorial');
Route::get('/odontograma/detalle/{idHistorial}', 'App\Http\Controllers\OdontogramaController@getDetalle');
Route::post('/odontograma/historial/fecha', 'App\Http\Controllers\OdontogramaController@getHistorialEntreFechas');
// Route::post('/odontograma/obtener', 'App\Http\Controllers\OdontogramaController@getOdontograma');
// Route::post('/odontograma/eliminar', 'App\Http\Controllers\OdontogramaController@eliminar');

//ANEXOS
Route::post('/tipoAnexos', 'App\Http\Controllers\AnexoController@TipoAnexos');
Route::post('/grabarAnexos/GrabarAnexos', 'App\Http\Controllers\AnexoController@GrabarAnexos');
Route::post('/anexos/getAnexosFecha', 'App\Http\Controllers\AnexoController@getAnexosFecha');
Route::post('/anexos/getAnexos', 'App\Http\Controllers\AnexoController@getAnexos');
Route::post('/anexos/deleteAnexos', 'App\Http\Controllers\AnexoController@deleteAnexos');

//MEDICOS
Route::post('/medicos/DatosFirmas', 'App\Http\Controllers\MedicosController@DatosFirmas');
Route::post('/medicos/getFirma', 'App\Http\Controllers\MedicosController@getFirma');

//CONSULTA
Route::post('/consulta/setConsulta', 'App\Http\Controllers\ConsultaController@guardarConsulta');
Route::post('/consulta/getEvolucionTratamiento', 'App\Http\Controllers\ConsultaController@getTratamientosPaciente');
Route::post('/consulta/setEvolucionTratamiento', 'App\Http\Controllers\ConsultaController@guardarEvolucionTratamiento');
Route::post('/consulta/deleteEvolucionTratamiento', 'App\Http\Controllers\ConsultaController@eliminarEvolucionTratamiento');
Route::post('/consulta/getEvolucionTratamientoOdonto', 'App\Http\Controllers\ConsultaController@getTratamientosPacienteOdonto');
Route::post('/consulta/setEvolucionTratamientoOdonto', 'App\Http\Controllers\ConsultaController@guardarEvolucionTratamientoOdonto');
Route::post('/consulta/deleteEvolucionTratamientoOdonto', 'App\Http\Controllers\ConsultaController@eliminarEvolucionTratamientoOdonto');
Route::post('/consulta/guardarSugerencias', 'App\Http\Controllers\ConsultaController@guardarSugerencias');
Route::post('/consulta/getSugerencias', 'App\Http\Controllers\ConsultaController@getSugerencias');
Route::post('/consulta/getHistoriaMedica', 'App\Http\Controllers\ConsultaController@listaHistoriaMedica');
Route::post('/consulta/getListaIgnorados', 'App\Http\Controllers\ConsultaController@getListaIgnorados');
Route::post('/consulta/getExamenesLaboratorio', 'App\Http\Controllers\ConsultaController@getExamenesLaboratorio');

Route::post('/procedimientos/getProcedimientos', 'App\Http\Controllers\ProcedimientosController@getProcedimientos');
Route::post('/procedimientos/setProcedimientos', 'App\Http\Controllers\ProcedimientosController@setProcedimientos');

Route::post('triaje/upsertTriaje', 'App\Http\Controllers\ConsultaController@upsertTriaje');
Route::post('triaje/getTriajeLista', 'App\Http\Controllers\ConsultaController@getListaTriaje');
Route::post('triaje/getTriaje', 'App\Http\Controllers\ConsultaController@traerTriaje');
Route::post('triaje/getExisteTriaje', 'App\Http\Controllers\ConsultaController@traerExistePacienteTriaje');

Route::post('pedido/getPedidoCabecera', 'App\Http\Controllers\ConsultaController@busquedaPedidoCabecera');
Route::post('pedido/getPedidoDetalles', 'App\Http\Controllers\ConsultaController@busquedaPedidoDetalles');
Route::post('pedido/verificacionPedido', 'App\Http\Controllers\ConsultaController@verificarPedido');

Route::post('orden/getOrdenCabecera', 'App\Http\Controllers\ConsultaController@busquedaOrdenCabecera');
Route::post('orden/getOrdenDetalles', 'App\Http\Controllers\ConsultaController@busquedaOrdenDetalles');

Route::post('comprobante/getComprobantesPago', 'App\Http\Controllers\ConsultaController@obtenerComprobantesPago');
Route::post('comprobante/getCorrelativoMontoNeto', 'App\Http\Controllers\ConsultaController@obtenerCorrelativoMontoNeto');
Route::post('atencionMedica/getEspecialidades', 'App\Http\Controllers\ConsultaController@obtenerEspecialidadConsultaMedico');
Route::post('atencionMedica/getConsultorios', 'App\Http\Controllers\ConsultaController@obtenerConsultorioConsultaMedico');
Route::post('atencionMedica/setConfirmarRecepcion', 'App\Http\Controllers\ConsultaController@setConfirmarRecepcion');
Route::post('atencionMedica/setConsultaMedica', 'App\Http\Controllers\ConsultaController@insertarAtencionMedica');

Route::post('atencionMedica/getTipoConsultaModulos', 'App\Http\Controllers\ConsultaController@obtenerTipoConsultaModulos');
Route::post('atencionMedica/setTriaje', 'App\Http\Controllers\ConsultaController@insertarTriaje');
Route::post('atencionMedica/setAnular', 'App\Http\Controllers\ConsultaController@anularConsultaMedica');
Route::post('atencionMedica/updateAtencion', 'App\Http\Controllers\ConsultaController@actualizarSolicitudAtencion');


// ADMIN
Route::post('/admin/getEspecialidades', 'App\Http\Controllers\AdminController@getEspecialidades');
Route::post('/admin/getListaAtenciones', 'App\Http\Controllers\AdminController@getListaAtenciones');
Route::post('/admin/getListaLiberados', 'App\Http\Controllers\AdminController@getListaLiberados');

// REPORTES
Route::post('/reportes/getReporte1', 'App\Http\Controllers\ReportesController@getReporte1');
Route::post('/reportes/getReporte2', 'App\Http\Controllers\ReportesController@getReporte2');
Route::post('/reportes/getReporte3', 'App\Http\Controllers\ReportesController@getReporte3');
Route::post('/reportes/getReporte4', 'App\Http\Controllers\ReportesController@getReporte4');
Route::post('/reportes/getTablasPrimarias', 'App\Http\Controllers\ReportesController@getTablasPrimarias');
Route::post('/reportes/getReporte4Detalle', 'App\Http\Controllers\ReportesController@getReporte4Detalle');

//MODULOS
Route::post('/modulos/getModulos', 'App\Http\Controllers\ModuloController@getModulos');
Route::post('/modulos/setModulos', 'App\Http\Controllers\ModuloController@editarModulos');
Route::post('/modulos/deleteModulos', 'App\Http\Controllers\ModuloController@eliminarModulos');
Route::post('/modulos/asignaModulos', 'App\Http\Controllers\ModuloController@asignacionModulos');
Route::post('/modulos/getMedicosModulos', 'App\Http\Controllers\ModuloController@getMedicosModulos');
Route::post('/modulos/getDataMedicos', 'App\Http\Controllers\ModuloController@getDataMedicos');


// CAMAS
Route::get('/camas/getPisos', 'App\Http\Controllers\CamasController@getPisos');
Route::get('/camas/getHabitaciones', 'App\Http\Controllers\CamasController@getHabitaciones');
Route::get('/camas/getCamas', 'App\Http\Controllers\CamasController@getCamas');
Route::post('/camas/createPiso', 'App\Http\Controllers\CamasController@createPiso');
Route::post('/camas/createHabitacion', 'App\Http\Controllers\CamasController@createHabitacion');
Route::post('/camas/createCama', 'App\Http\Controllers\CamasController@createCama');
Route::post('/camas/editPiso', 'App\Http\Controllers\CamasController@editPiso');
Route::post('/camas/editCama', 'App\Http\Controllers\CamasController@editCama');
Route::post('/camas/editHabitacion', 'App\Http\Controllers\CamasController@editHabitacion');
Route::post('/camas/deletePiso', 'App\Http\Controllers\CamasController@deletePiso');
Route::post('/camas/deleteCama', 'App\Http\Controllers\CamasController@deleteCama');
Route::post('/camas/deleteHabitacion', 'App\Http\Controllers\CamasController@deleteHabitacion');
Route::post('/camas/changeEstado', 'App\Http\Controllers\CamasController@changeEstado');
Route::post('/camas/asignacionCama', 'App\Http\Controllers\CamasController@asignacionCama');
Route::post('/camas/liberarCama', 'App\Http\Controllers\CamasController@liberarCama');
Route::post('/camas/getPacientes', 'App\Http\Controllers\CamasController@getPacientes');
Route::get('/camas/getEspecilidades', 'App\Http\Controllers\CamasController@getEspecilidades');
Route::get('/camas/getMotivos', 'App\Http\Controllers\CamasController@getMotivos');
Route::post('/camas/transferirCama', 'App\Http\Controllers\CamasController@transferirCama');
Route::post('/camas/devolverCama', 'App\Http\Controllers\CamasController@devolverCama');

// FIRMAS
Route::get('/firmas/getFirmas', 'App\Http\Controllers\DatosFirmasController@getFirmas');
Route::post('/firmas/getFirma', 'App\Http\Controllers\DatosFirmasController@getFirma');
Route::post('/firmas/createFirma', 'App\Http\Controllers\DatosFirmasController@createFirma');
Route::post('/firmas/updateFirma', 'App\Http\Controllers\DatosFirmasController@updateFirma');
Route::post('/firmas/deleteFirma', 'App\Http\Controllers\DatosFirmasController@deleteFirma');

// BALANCE HIDRICO
Route::get('/balance/getEstaciones', 'App\Http\Controllers\EstacionController@getEstaciones');
Route::post('/balance/createBalanceHidrico', 'App\Http\Controllers\BalanceHidricoController@createBalanceHidrico');
Route::post('/balance/updateBalanceHidrico', 'App\Http\Controllers\BalanceHidricoController@updateBalanceHidrico');
Route::post('/balance/getOneBalanceHidrico', 'App\Http\Controllers\BalanceHidricoController@getOneBalanceHidrico');
Route::post('/balance/getHistoryBalanceHidrico', 'App\Http\Controllers\BalanceHidricoController@getHistoryBalanceHidrico');

// SIGNOS VITALES
Route::post('/vitales/getRangeSignosVitales', 'App\Http\Controllers\SignosVitalesController@getRangeSignosVitales');
Route::post('/vitales/getOneSignosVitales', 'App\Http\Controllers\SignosVitalesController@getOneSignosVitales');
Route::post('/vitales/createSignosVitales', 'App\Http\Controllers\SignosVitalesController@createSignosVitales');
Route::post('/vitales/updateSignosVitales', 'App\Http\Controllers\SignosVitalesController@updateSignosVitales');

// KARDEX
Route::post('/kardex/getKardex', 'App\Http\Controllers\KardexController@getKardex');
Route::post('/kardex/getHistorialKardex', 'App\Http\Controllers\KardexController@getKardexHistorialTratamiento');
Route::post('/kardex/getFechaAtencion', 'App\Http\Controllers\KardexController@getFechaAtencion');
Route::post('/kardex/setKardexTratamiento', 'App\Http\Controllers\KardexController@setKardexTratamiento');
Route::post('/kardex/setKardexExamen', 'App\Http\Controllers\KardexController@setKardexExamen');
Route::post('/kardex/setKardexInterconsulta', 'App\Http\Controllers\KardexController@setKardexInterconsulta');
Route::post('/kardex/setKardexEspecial', 'App\Http\Controllers\KardexController@setKardexEspecial');

// REPORTES AUDITORIA
Route::post('/auditoria/getAuditoria', 'App\Http\Controllers\ReportController@getAuditoria');
Route::post('/auditoria/getAuditoriaxEspecialidad', 'App\Http\Controllers\ReportController@getAuditoriaEspecialidades');
Route::post('/auditoria/getPesoEspecialidades', 'App\Http\Controllers\ReportController@obtenerPesoEspecialidades');
Route::post('/auditoria/getEspecialidades', 'App\Http\Controllers\ReportController@getEspecialidades');

// EVOLUCION DE ENFERMERIA
Route::post('/evolucionEnfermeria/setData', 'App\Http\Controllers\EvolucionEnfermeriaController@agregarEvolucionEnfermeria');
Route::post('/evolucionEnfermeria/getByFecha', 'App\Http\Controllers\EvolucionEnfermeriaController@filtrarEEPorFecha');
Route::post('/evolucionEnfermeria/getPacientes', 'App\Http\Controllers\EvolucionEnfermeriaController@getPacientes');
Route::post('/evolucionEnfermeria/getMedicos', 'App\Http\Controllers\EvolucionEnfermeriaController@getMedicos');

// PRE TRIAJE
Route::post('/preTriaje/setPreTriaje', 'App\Http\Controllers\ConsultaController@generarPreTriaje');
Route::post('/preTriaje/searchPreTriaje', 'App\Http\Controllers\ConsultaController@busquedaPreTriaje');
Route::post('/preTriaje/searchPacientes', 'App\Http\Controllers\ConsultaController@busquedaPreTriajePacientes');
Route::post('/preTriaje/searchMedicos', 'App\Http\Controllers\ConsultaController@busquedaPreTriajeMedicos');

// VERSION SISTEMA
Route::post('/sistema/getVersion', 'App\Http\Controllers\AdminController@obtenerVersionSistemaWeb');

// kardex hospitalario
Route::post('/kardex/addInvacivos', 'App\Http\Controllers\KardexController@addInvasivos');
Route::post('/kardex/getInvacivos', 'App\Http\Controllers\KardexController@getInvasivos');

// Pos Venta
Route::post('/posventa/getProductos', 'App\Http\Controllers\PosVentaController@obtenerListaProductos');
Route::post('/posventa/getEspecialidades', 'App\Http\Controllers\PosVentaController@obtenerListaEspecialidades');
Route::post('/posventa/getCajaDispoUsuario', 'App\Http\Controllers\PosVentaController@obtenerCajaDispoUsuario');
Route::post('/posventa/getFechaMovCaja', 'App\Http\Controllers\PosVentaController@obtenerFechaMovCaja');
Route::get('/posventa/getFechaHoraDB', 'App\Http\Controllers\PosVentaController@obtenerFechaHoraDB');
Route::post('/posventa/validaOperacionCaja', 'App\Http\Controllers\PosVentaController@validaOperadorCaja');
Route::post('/posventa/getValorCompBoleta', 'App\Http\Controllers\PosVentaController@obtenerValorCompBoleta');
Route::post('/posventa/getValorCompFactura', 'App\Http\Controllers\PosVentaController@obtenerValorCompFactura');
Route::post('/posventa/getSeriesBoleta', 'App\Http\Controllers\PosVentaController@obtenerListaSeriesBoleta');
Route::post('/posventa/getSeriesFactura', 'App\Http\Controllers\PosVentaController@obtenerListaSeriesFactura');
Route::post('/posventa/getMovApertura', 'App\Http\Controllers\PosVentaController@obtenerMovApertura');
Route::post('/posventa/setBloqueoCaja', 'App\Http\Controllers\PosVentaController@setBloqueoCaja');
Route::post('/posventa/procesarDatosArqueo', 'App\Http\Controllers\PosVentaController@procesaDatosArqueo');
Route::post('/posventa/aceptarTransaccion', 'App\Http\Controllers\PosVentaController@aceptarTransaccion');
Route::post('/posventa/updateNumera', 'App\Http\Controllers\PosVentaController@updateNumera');
Route::post('/posventa/getTurnoActualCaja', 'App\Http\Controllers\PosVentaController@obtenerTurnoActualCaja');
Route::post('/posventa/getFechaApertura', 'App\Http\Controllers\PosVentaController@obtenerFechaApertura');
Route::post('/posventa/setRegistraMovimientoAper', 'App\Http\Controllers\PosVentaController@setRegistraMovimientoApertura');

// Productos
Route::post('/posventa/getValidoVerPrecioMinimo', 'App\Http\Controllers\PosVentaController@isValidoVerPrecioMinimo');
Route::post('/posventa/getIndSolIdUsu', 'App\Http\Controllers\PosVentaController@obtenerIndSolIdUsu');
Route::post('/posventa/getListaFracciones', 'App\Http\Controllers\PosVentaController@obtenerListaFracciones');
Route::post('/posventa/getListaLoteProducto', 'App\Http\Controllers\PosVentaController@obtenerListaLoteProducto');
Route::post('/posventa/getDetalleCompProducto', 'App\Http\Controllers\PosVentaController@obtenerInfoDetalleProducto');
Route::post('/posventa/verificarProductoCamp', 'App\Http\Controllers\PosVentaController@verificaProdCamp');
Route::post('/posventa/getNuevoPrecio', 'App\Http\Controllers\PosVentaController@obtenerNuevoPrecio');
Route::post('/posventa/getPrecioRedondeado', 'App\Http\Controllers\PosVentaController@obtenerPrecioRedondeado');

