<?php

use App\Http\Controllers\Api\AssuntoController;
use App\Http\Controllers\Api\AutorController;
use App\Http\Controllers\Api\LivroController;
use App\Http\Controllers\Api\RelatorioController;
use Illuminate\Support\Facades\Route;

Route::get('/livros', [LivroController::class, 'index']);
Route::post('/livros', [LivroController::class, 'store']);
Route::get('/livros/{codl}', [LivroController::class, 'show']);
Route::put('/livros/{codl}', [LivroController::class, 'update']);
Route::delete('/livros/{codl}', [LivroController::class, 'destroy']);

Route::get('/autores', [AutorController::class, 'index']);
Route::post('/autores', [AutorController::class, 'store']);
Route::get('/autores/{codAu}', [AutorController::class, 'show']);
Route::put('/autores/{codAu}', [AutorController::class, 'update']);
Route::delete('/autores/{codAu}', [AutorController::class, 'destroy']);

Route::get('/assuntos', [AssuntoController::class, 'index']);
Route::post('/assuntos', [AssuntoController::class, 'store']);
Route::get('/assuntos/{codAs}', [AssuntoController::class, 'show']);
Route::put('/assuntos/{codAs}', [AssuntoController::class, 'update']);
Route::delete('/assuntos/{codAs}', [AssuntoController::class, 'destroy']);

Route::get('/relatorios/livros-por-autor', [RelatorioController::class, 'livrosPorAutor']);
