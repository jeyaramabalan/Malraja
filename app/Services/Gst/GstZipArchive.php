<?php

namespace App\Services\Gst;

use PDF;
use ZipArchive;

class GstZipArchive
{
    public static function create(array $entries): string
    {
        $path = tempnam(sys_get_temp_dir(), 'gst_zip_');
        $zipPath = $path . '.zip';
        @unlink($path);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create ZIP archive');
        }

        foreach ($entries as $entry) {
            $zip->addFromString($entry['name'], $entry['content']);
        }
        $zip->close();

        $content = file_get_contents($zipPath);
        @unlink($zipPath);

        return $content ?: '';
    }

    public static function pdfFromView(string $view, array $data): string
    {
        return PDF::loadView($view, $data)->output();
    }
}
