<?php

use Illuminate\Support\Facades\Route;
//AUTH
Route::post('/login', 'App\Http\Controllers\AuthController@login');

// REPORTES
Route::post('/reportes/getReporte1', 'App\Http\Controllers\ReportController@getReporte1');
Route::post('/reportes/getReporte2', 'App\Http\Controllers\ReportController@getReporte2');
Route::post('/reportes/getReporte3', 'App\Http\Controllers\ReportController@getReporte3');
Route::post('/reportes/getReporte4', 'App\Http\Controllers\ReportController@getReporte4');
Route::post('/reportes/getTablasPrimarias', 'App\Http\Controllers\ReportController@getTablasPrimarias');
Route::post('/reportes/getReporte4Detalle', 'App\Http\Controllers\ReportController@getReporte4Detalle');
