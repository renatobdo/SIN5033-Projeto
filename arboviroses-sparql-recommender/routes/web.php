<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\DB;

// Arquivos principais para um esqueleto Laravel integrando Fuseki SPARQL

// 1. Rotas - routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SPARQLController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\RecursoController;

use App\Http\Controllers\SituacaoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/quiz', [QuizController::class, 'show'])->middleware('auth');
Route::post('/quiz', [QuizController::class, 'submit'])->middleware('auth');

Route::post('/preferencia', [SPARQLController::class, 'registrarPreferencia']);
Route::post('/salvar-preferencia', [SPARQLController::class, 'salvarPreferencia']);


Route::get('/dashboard', [SPARQLController::class, 'dashboard'])->middleware('auth')->name('dashboard');
Route::get('/recomendacoes', [SPARQLController::class, 'recommend'])->middleware('auth')->name('recomendacoes');

Route::get('/preferencias/{email}', [PreferenciasController::class, 'obterPreferencias']);


Route::get('/situacao', [SituacaoController::class, 'index'])->middleware('auth')->name('situacao');
Route::middleware('auth')->group(function () {
    Route::get('/avaliacao', [AvaliacaoController::class, 'index'])->name('avaliacao.index');
    Route::post('/avaliacao', [AvaliacaoController::class, 'store'])->name('avaliacao.store');

});


Route::get('/teste-medias', function () {
    $medias = DB::table('notas')
        ->select('recurso_uri', DB::raw('AVG(nota) as media'))
        ->groupBy('recurso_uri')
        ->get();

    foreach ($medias as $item) {
        echo "Recurso: {$item->recurso_uri} — Média: " . number_format($item->media, 2) . "<br>";
    }
});
Route::get('/recursos', [RecursoController::class, 'index'])->name('recursos.index');
Route::post('/recursos/acessar', [RecursoController::class, 'acessar'])->name('recursos.acessar');