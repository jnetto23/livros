<?php

namespace App\Http\Controllers;

use App\Application\Usecases\Commands\CreateAutorCommand;
use App\Application\Usecases\Commands\DeleteAutorCommand;
use App\Application\Usecases\Commands\UpdateAutorCommand;
use App\Application\Usecases\Queries\FindAutorByIdQuery;
use App\Application\Usecases\Queries\ListAutoresQuery;
use App\Domain\Entity\Autor;
use App\Http\Handlers\ExceptionHandler;
use App\Http\Resources\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AutorController extends Controller
{
    public function index(Request $request, ListAutoresQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\ListAutoresInputDTO(
                search: $request->input('search'),
                sort: $request->input('sort'),
                dir: $request->input('dir'),
                page: $request->input('page') !== null ? (int) $request->input('page') : null,
                limit: $request->input('limit') !== null ? (int) $request->input('limit') : null
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAutoresOutputDTO);

            return ApiResource::success([
                'data' => array_map(fn(Autor $autor) => $this->toArray($autor), $output->autores()),
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

    public function store(Request $request, CreateAutorCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => ['required', 'string', 'max:255'],
            ]);

            $input = new \App\Application\Usecases\Commands\CreateAutorInputDTO(
                nome: $validated['nome']
            );

            $output = $command->execute($input);
            assert($output instanceof \App\Application\Usecases\Commands\CreateAutorOutputDTO);

            return ApiResource::created(['codau' => $output->codau], 'Autor criado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function show(int $codau, FindAutorByIdQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\FindAutorByIdInputDTO(codau: $codau);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindAutorByIdOutputDTO);

            if ($output->autor === null) {
                return ApiResource::error('Autor não encontrado', 404);
            }

            return ApiResource::success(['data' => $this->toArray($output->autor)]);
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function update(Request $request, int $codau, UpdateAutorCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => ['required', 'string', 'max:255'],
            ]);

            $input = new \App\Application\Usecases\Commands\UpdateAutorInputDTO(
                codau: $codau,
                nome: $validated['nome']
            );

            $command->execute($input);

            return ApiResource::success([], 'Autor atualizado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function destroy(int $codau, DeleteAutorCommand $command): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Commands\DeleteAutorInputDTO(codau: $codau);
            $command->execute($input);

            return ApiResource::noContent();
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    private function toArray(Autor $autor): array
    {
        return [
            'codau' => $autor->codau(),
            'nome' => $autor->nome()->value(),
        ];
    }
}

