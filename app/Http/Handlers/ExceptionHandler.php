<?php

namespace App\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class ExceptionHandler
{
    /**
     * Mapeia exceções para status codes HTTP
     */
    public static function handle(\Throwable $exception, string $operation = 'operation'): JsonResponse
    {
        // Erros de validação do Laravel
        if ($exception instanceof ValidationException) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $exception->errors()
            ], 422);
        }

        // Erros de validação de parâmetros
        if ($exception instanceof \InvalidArgumentException) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        // Erros de não encontrado
        if ($exception instanceof \RuntimeException) {
            // Verifica se é um erro de "não encontrado"
            if (str_contains($exception->getMessage(), 'não encontrado') ||
                str_contains($exception->getMessage(), 'não encontrada')) {
                return response()->json(['error' => $exception->getMessage()], 404);
            }
            // Outros RuntimeExceptions são erros de aplicação
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        // Erros de domínio (regras de negócio)
        if ($exception instanceof \DomainException) {
            $message = $exception->getMessage();

            // Verifica se é um conflito (duplicatas, etc)
            if (self::isConflict($message)) {
                return response()->json(['error' => $message], 409);
            }

            // Outros erros de domínio são UNPROCESSABLE_CONTENT
            return response()->json(['error' => $message], 422);
        }

        // Erros de banco de dados (PDO, QueryException, etc)
        if ($exception instanceof \PDOException ||
            $exception instanceof \Illuminate\Database\QueryException) {
            return response()->json([
                'error' => 'Erro ao processar operação no banco de dados'
            ], 500);
        }

        // Erros não esperados
        return response()->json([
            'error' => 'Erro interno do servidor'
        ], 500);
    }

    /**
     * Verifica se a mensagem indica um conflito (duplicatas, etc)
     */
    private static function isConflict(string $message): bool
    {
        $conflictKeywords = [
            'já existe',
            'já existem',
            'duplicado',
            'duplicada',
            'conflito',
            'já foi',
            'já está',
        ];

        $messageLower = mb_strtolower($message);

        foreach ($conflictKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return true;
            }
        }

        return false;
    }
}

