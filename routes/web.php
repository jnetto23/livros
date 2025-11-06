<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;
use App\Http\Controllers\ReportController;

Route::get('/relatorio/livros-por-autor', [ReportController::class, 'livrosPorAutor'])->name('report.livros-por-autor');
Route::get('/', HomePage::class)->name('home');
