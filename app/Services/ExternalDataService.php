<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ExternalDataService
{
    protected string $endpoint = 'https://bit.ly/48ejMhW';

    public function getAll(): array
    {

        $data = rescue(
            fn() => Http::connectTimeout(2)
                ->timeout(5)
                ->retry(3, 200)
                ->get($this->endpoint)
                ->json('DATA'),
            report: true
        );

        $lines = Str::of($data ?? '')->trim()->split('/\r\n|\r|\n/');

        $header = array_map('trim', explode('|', $lines->shift()));

        $rows = $lines
            ->filter(fn(string $line) => trim($line) !== '')
            ->map(fn(string $line) => array_map('trim', explode('|', $line)))
            ->filter(fn(array $values) => count($values) === count($header))
            ->map(function (array $values) use ($header) {
                $row = array_combine($header, $values);

                return [
                    'nim' => $row['NIM'] ?? null,
                    'nama' => $row['NAMA'] ?? null,
                    'tanggal_lahir' => $row['YMD'] ?? null,
                ];
            })
            ->values()
            ->all();

        throw_if(
            empty($rows),
            RuntimeException::class,
            'Gagal mengambil data dari sumber eksternal setelah beberapa percobaan'
        );

        return $rows;
    }

    public function searchByNama(string $keyword): array
    {
        return collect($this->getAll())
            ->whereNotNull('nama')
            ->filter(fn(array $row) => stripos($row['nama'], $keyword) !== false)
            ->values()
            ->all();
    }

    public function searchByNim(string $nim): array
    {
        return collect($this->getAll())
            ->where('nim', $nim)
            ->values()
            ->all();
    }

    public function searchByYmd(string $ymd): array
    {
        return collect($this->getAll())
            ->where('tanggal_lahir', $ymd)
            ->values()
            ->all();
    }
}
