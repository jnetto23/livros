<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ReportController extends Controller
{
    public function livrosPorAutor(Request $request)
    {
        // Busca já agregada em JSON
        $rows = DB::table('autores_livros_json')
            ->orderBy('autor_nome')
            ->get();

        // Normaliza em array para a view
        $autores = [];
        foreach ($rows as $row) {
            $livros = json_decode($row->livros ?? '[]', true) ?: [];

            // Garante estrutura e tipos
            $livros = array_map(function (array $l) {
                return [
                    'codl'          => (int)   ($l['codl'] ?? 0),
                    'titulo'        => (string)($l['titulo'] ?? ''),
                    'editora'       => (string)($l['editora'] ?? ''),
                    'edicao'        => (int)   ($l['edicao'] ?? 0),
                    'anoPublicacao' => (string)($l['anoPublicacao'] ?? ''),
                    'valor'         => (int)   ($l['valor'] ?? 0),
                    'assuntos'      => array_values(array_filter((array)($l['assuntos'] ?? []), fn($v) => $v !== null && $v !== '')),
                ];
            }, $livros);

            $autores[] = [
                'nome'   => (string)$row->autor_nome,
                'livros' => $livros,
            ];
        }

        // Renderiza PDF
        $pdf = Pdf::loadView('reports.livros-por-autor', [
            'autores'     => $autores,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'portrait');

        // ?inline=1 pra abrir no navegador; default download
        if ($request->boolean('inline')) {
            return $pdf->stream('relatorio-livros-por-autor.pdf');
        }

        return $pdf->download('relatorio-livros-por-autor.pdf');
    }
}
