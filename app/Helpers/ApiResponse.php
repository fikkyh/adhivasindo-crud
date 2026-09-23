<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = null, string $message = 'Berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'success' => true,
                'code'    => $code,
                'message' => $message,
            ],
        ], $code);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $message = 'Berhasil'): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'success' => true,
                'code'    => 200,
                'message' => $message,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
            ],
        ]);
    }

    public static function error(string $message = 'Terjadi kesalahan', int $code = 400): JsonResponse
    {
        return response()->json([
            'meta' => [
                'success' => false,
                'code'    => $code,
                'message' => $message,
            ],
            'data' => null,
        ], $code);
    }
}
