<?php

use App\Http\Controllers\AssuntoController;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\LivroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Assuntos
Route::apiResource('assuntos', AssuntoController::class)->parameters([
    'assuntos' => 'codas'
]);

// Autores
Route::apiResource('autores', AutorController::class)->parameters([
    'autores' => 'codau'
]);

// Livros
Route::apiResource('livros', LivroController::class)->parameters([
    'livros' => 'codl'
]);

