<?php

namespace App\Http\Controllers;

use App\Application\Usecases\Commands\CreateAssuntoCommand;
use App\Application\Usecases\Commands\DeleteAssuntoCommand;
use App\Application\Usecases\Commands\UpdateAssuntoCommand;
use App\Application\Usecases\Queries\FindAssuntoByIdQuery;
use App\Application\Usecases\Queries\ListAssuntosQuery;
use App\Domain\Entity\Assunto;
use App\Http\Handlers\ExceptionHandler;
use App\Http\Resources\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AssuntoController extends Controller
{
    public function index(Request $request, ListAssuntosQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\ListAssuntosInputDTO(
                search: $request->input('search'),
                sort: $request->input('sort'),
                dir: $request->input('dir'),
                page: $request->input('page') !== null ? (int) $request->input('page') : null,
                limit: $request->input('limit') !== null ? (int) $request->input('limit') : null
            );

            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\ListAssuntosOutputDTO);

            return ApiResource::success([
                'data' => array_map(fn(Assunto $assunto) => $this->toArray($assunto), $output->assuntos()),
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

    public function store(Request $request, CreateAssuntoCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'descricao' => ['required', 'string', 'max:255'],
            ]);

            $input = new \App\Application\Usecases\Commands\CreateAssuntoInputDTO(
                descricao: $validated['descricao']
            );

            $output = $command->execute($input);
            assert($output instanceof \App\Application\Usecases\Commands\CreateAssuntoOutputDTO);

            return ApiResource::created(['codas' => $output->codas], 'Assunto criado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function show(int $codas, FindAssuntoByIdQuery $query): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Queries\FindAssuntoByIdInputDTO(codas: $codas);
            $output = $query->execute($input);
            assert($output instanceof \App\Application\Usecases\Queries\FindAssuntoByIdOutputDTO);

            if ($output->assunto === null) {
                return ApiResource::error('Assunto não encontrado', 404);
            }

            return ApiResource::success(['data' => $this->toArray($output->assunto)]);
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function update(Request $request, int $codas, UpdateAssuntoCommand $command): JsonResponse
    {
        try {
            $validated = $request->validate([
                'descricao' => ['required', 'string', 'max:255'],
            ]);

            $input = new \App\Application\Usecases\Commands\UpdateAssuntoInputDTO(
                codas: $codas,
                descricao: $validated['descricao']
            );

            $command->execute($input);

            return ApiResource::success([], 'Assunto atualizado com sucesso');
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    public function destroy(int $codas, DeleteAssuntoCommand $command): JsonResponse
    {
        try {
            $input = new \App\Application\Usecases\Commands\DeleteAssuntoInputDTO(codas: $codas);
            $command->execute($input);

            return ApiResource::noContent();
        } catch (\Throwable $e) {
            return ExceptionHandler::handle($e);
        }
    }

    private function toArray(Assunto $assunto): array
    {
        return [
            'codas' => $assunto->codas(),
            'descricao' => $assunto->descricao()->value(),
        ];
    }
}

