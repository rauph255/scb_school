<?php

namespace App\Support;

use DateTimeInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SafeCsvExporter
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $stream = fopen('php://output', 'wb');

            if ($stream === false) {
                return;
            }

            try {
                fputcsv($stream, array_map($this->cell(...), $headings), ',', '"', '');

                foreach ($rows as $row) {
                    fputcsv($stream, array_map($this->cell(...), $row), ',', '"', '');
                }
            } finally {
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function cell(mixed $value): string
    {
        $cell = match (true) {
            $value === null => '',
            $value instanceof DateTimeInterface => $value->format(DATE_ATOM),
            is_bool($value) => $value ? 'Yes' : 'No',
            default => (string) $value,
        };

        return preg_match('/^[=+\-@\t\r]/u', $cell) === 1 ? "'".$cell : $cell;
    }
}
