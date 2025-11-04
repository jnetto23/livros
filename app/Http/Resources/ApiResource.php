<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

final class ApiResource
{
    public static function success(array|object $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        $response = [];

        if ($message !== null) {
            $response['message'] = $message;
        }

        // Se $data já contém a estrutura completa (com 'data' e 'pagination'), usa diretamente
        if (is_array($data) && (isset($data['data']) || isset($data['pagination']))) {
            $response = array_merge($response, $data);
        } elseif (!empty($data)) {
            $response['data'] = is_array($data) ? $data : (array) $data;
        }

        return response()->json($response, $status);
    }

    public static function created(array $data = [], ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? 'Criado com sucesso', 201);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json([], 204);
    }

    public static function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }
}

