<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;
use App\Http\Controllers\ReportController;

// "/" redireciona para /subjects (opcional)
Route::redirect('/', '/livros');

// Rotas amigáveis, todas apontando pro mesmo componente, mudando "section"
Route::get('/assuntos', HomePage::class)->name('subjects')->defaults('section', 'subjects');
Route::get('/autores',  HomePage::class)->name('authors')->defaults('section', 'authors');
Route::get('/livros',    HomePage::class)->name('books')->defaults('section', 'books');

// Relatórios
Route::get('/relatorio/livros-por-autor', [ReportController::class, 'livrosPorAutor'])->name('report.livros-por-autor');
