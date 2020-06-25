<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('api/login', 'UserController@login');

Route::get('api/ordencompra-por-periodo/{periodo}/{estado}/{filtroRango}/{textoBusqueda}', 'DocVentasController@listado_por_periodo_y_estado');

Route::get('api/ordencompra-detalle/{idserie}/{iddocventas}', 'DocVentasController@detalle');

Route::put('api/ordencompra-procesar/{accion}', 'DocVentasController@procesar');