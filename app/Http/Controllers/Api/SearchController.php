<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\ExternalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function __construct(protected ExternalDataService $externalData) {}

    public function byNama(Request $request): JsonResponse
    {
        $request->validate(['nama' => ['required', 'string']]);

        $keyword = $request->query('nama');

        try {
            $result = $this->externalData->searchByNama($keyword);
        } catch (Throwable $e) {
            return ApiResponse::error('Gagal mengambil data dari sumber eksternal, coba lagi', 503);
        }

        return ApiResponse::success($result, 'Hasil pencarian berdasarkan NAMA: ' . $keyword);
    }

    public function byNim(Request $request): JsonResponse
    {
        $request->validate(['nim' => ['required', 'string']]);

        $keyword = $request->query('nim');

        try {
            $result = $this->externalData->searchByNim($keyword);
        } catch (Throwable $e) {
            return ApiResponse::error('Gagal mengambil data dari sumber eksternal, coba lagi', 503);
        }

        return ApiResponse::success($result, 'Hasil pencarian berdasarkan NIM: ' . $keyword);
    }

    public function byYmd(Request $request): JsonResponse
    {
        $request->validate(['ymd' => ['required', 'string']]);

        $keyword = $request->query('ymd');

        try {
            $result = $this->externalData->searchByYmd($keyword);
        } catch (Throwable $e) {
            return ApiResponse::error('Gagal mengambil data dari sumber eksternal, coba lagi', 503);
        }

        return ApiResponse::success($result, 'Hasil pencarian berdasarkan YMD: ' . $keyword);
    }
}
