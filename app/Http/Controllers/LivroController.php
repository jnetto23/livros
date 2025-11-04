<?php

namespace App\Http\Controllers;

use App\Application\Usecases\Commands\CreateLivroCommand;
use App\Application\Usecases\Commands\DeleteLivroCommand;
use App\Application\Usecases\Commands\UpdateLivroCommand;
use App\Application\Usecases\Queries\FindLivroByIdQuery;
use App\Application\Usecases\Queries\ListLivrosQuery;
use App\Domain\Entity\Livro;
use App\Http\Handlers\ExceptionHandler;
use App\Http\Resources\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LivroController extends Controller
{
    public function index(Request $request, ListLivrosQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\ListLivrosInputDTO(
                search: $request->input('search'),
                sort: $request->input('sort'),
                dir: $request->input('dir'),
                page: $request->input('page') !== null ? (int) $request->input('page') : null,
                limit: $request->input('limit') !== null ? (int) $request->input('limit') : null
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListLivrosOutputDTO);

            return ApiResource::success([
                'data' => array_map(fn(Livro $livro) => $this->toArray($livro), $output->livros()),
                'pagination' => [
                    'total' => $output->total,
                    'page' => $output->page,
                    'limit' => $output->limit,
                    'totalPages' => $output->totalPages,
                ]
            ]);
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function store(Request $request, CreateLivroCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => ['required', 'string', 'max:255'],
                'editora' => ['required', 'string', 'max:255'],
                'edicao' => ['required', 'integer', 'min:1'],
                'anoPublicacao' => ['required', 'string', 'size:4'],
                'valor' => ['required', 'integer', 'min:0'],
                'autoresIds' => ['array'],
                'autoresIds.*' => ['integer', 'exists:autor,codau'],
                'assuntosIds' => ['array'],
                'assuntosIds.*' => ['integer', 'exists:assunto,codas'],
            ]);

            $input = new \App\Application\Usecases\Commands\CreateLivroInputDTO(
                titulo: $validated['titulo'],
                editora: $validated['editora'],
                edicao: $validated['edicao'],
                anoPublicacao: $validated['anoPublicacao'],
                valor: $validated['valor'],
                autoresIds: $validated['autoresIds'] ?? [],
                assuntosIds: $validated['assuntosIds'] ?? []
            );

            $output = $command->execute($input);
            assert($output instanceof \App\Application\Usecases\Commands\CreateLivroOutputDTO);

            return ApiResource::created(['codl' => $output->codl], 'Livro criado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function show(int $codl, FindLivroByIdQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\FindLivroByIdInputDTO(codl: $codl);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindLivroByIdOutputDTO);

            if ($output->livro === null) {
                return ApiResource::error('Livro não encontrado', 404);
            }

            return ApiResource::success(['data' => $this->toArray($output->livro)]);
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function update(Request $request, int $codl, UpdateLivroCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => ['required', 'string', 'max:255'],
                'editora' => ['required', 'string', 'max:255'],
                'edicao' => ['required', 'integer', 'min:1'],
                'anoPublicacao' => ['required', 'string', 'size:4'],
                'valor' => ['required', 'integer', 'min:0'],
                'autoresIds' => ['array'],
                'autoresIds.*' => ['integer', 'exists:autor,codau'],
                'assuntosIds' => ['array'],
                'assuntosIds.*' => ['integer', 'exists:assunto,codas'],
            ]);

            $input = new \App\Application\Usecases\Commands\UpdateLivroInputDTO(
                codl: $codl,
                titulo: $validated['titulo'],
                editora: $validated['editora'],
                edicao: $validated['edicao'],
                anoPublicacao: $validated['anoPublicacao'],
                valor: $validated['valor'],
                autoresIds: $validated['autoresIds'] ?? [],
                assuntosIds: $validated['assuntosIds'] ?? []
            );

            $command->execute($input);

            return ApiResource::success([], 'Livro atualizado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function destroy(int $codl, DeleteLivroCommand $command): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Commands\DeleteLivroInputDTO(codl: $codl);
            $command->execute($input);

            return ApiResource::noContent();
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    private function toArray(Livro $livro): array
    {
        return [
            'codl' => $livro->codl(),
            'titulo' => $livro->titulo()->value(),
            'editora' => $livro->editora()->value(),
            'edicao' => $livro->edicao()->value(),
            'anoPublicacao' => $livro->anoPublicacao()->value(),
            'valor' => $livro->valor()->value(),
            'autoresIds' => $livro->autoresIds(),
            'assuntosIds' => $livro->assuntosIds(),
        ];
    }
}

