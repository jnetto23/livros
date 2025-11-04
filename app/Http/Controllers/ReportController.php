<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

final class ReportController extends Controller
{
    public function livrosPorAutor()
    {
        $dados = DB::table('autores_livros')
            ->orderBy('autor_nome')
            ->get();

        // Processa os dados para facilitar a renderização
        $autores = [];
        foreach ($dados as $row) {
            $livros = [];
            $livrosRaw = explode(' | ', $row->livros);

            foreach ($livrosRaw as $livroRaw) {
                $parts = explode(',', $livroRaw);
                if (count($parts) >= 7) {
                    $assuntos = array_pop($parts); // Remove último elemento (assuntos)
                    $valor = (int) array_pop($parts); // Remove penúltimo (valor)
                    $anoPublicacao = array_pop($parts);
                    $edicao = (int) array_pop($parts);
                    $editora = array_pop($parts);
                    $titulo = array_pop($parts);
                    $codl = (int) array_pop($parts);

                    $livros[] = [
                        'codl' => $codl,
                        'titulo' => $titulo,
                        'editora' => $editora,
                        'edicao' => $edicao,
                        'anoPublicacao' => $anoPublicacao,
                        'valor' => $valor,
                        'assuntos' => $assuntos !== 'Sem assunto' ? explode(';', $assuntos) : [],
                    ];
                }
            }

            $autores[] = [
                'nome' => $row->autor_nome,
                'livros' => $livros,
            ];
        }

        $pdf = Pdf::loadView('reports.livros-por-autor', [
            'autores' => $autores,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
        ]);

        return $pdf->download('relatorio-livros-por-autor.pdf');
    }
}

