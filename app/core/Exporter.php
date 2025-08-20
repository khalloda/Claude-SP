<?php
declare(strict_types=1);

namespace App\Core;

class Exporter
{
    public static function csv(array $data, array $headers = [], string $filename = null): void
    {
        if ($filename) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Write headers
        if (!empty($headers)) {
            fputcsv($output, $headers);
        } elseif (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    }

    public static function generateCsvString(array $data, array $headers = []): string
    {
        ob_start();
        
        $output = fopen('php://output', 'w');

        // Write headers
        if (!empty($headers)) {
            fputcsv($output, $headers);
        } elseif (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        
        return ob_get_clean();
    }
}
