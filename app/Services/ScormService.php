<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ScormService
{
    /**
     * Extract a SCORM ZIP package and return manifest info.
     *
     * @return array{version: string, entry_point: string, package_path: string}
     * @throws \RuntimeException
     */
    public function extractPackage(UploadedFile $file, int $courseId, int $lessonId): array
    {
        $zip = new ZipArchive();
        $tempPath = $file->getRealPath();

        if ($zip->open($tempPath) !== true) {
            throw new \RuntimeException('Could not open SCORM package ZIP file.');
        }

        // Look for imsmanifest.xml
        $manifestIndex = $zip->locateName('imsmanifest.xml', ZipArchive::FL_NODIR);
        if ($manifestIndex === false) {
            $zip->close();
            throw new \RuntimeException('Invalid SCORM package: imsmanifest.xml not found.');
        }

        $manifestContent = $zip->getFromIndex($manifestIndex);
        $zip->close();

        // Parse manifest
        $manifest = $this->parseManifest($manifestContent);

        // Extract to storage
        $packageDir = "scorm/course_{$courseId}/lesson_{$lessonId}";
        $fullPath = storage_path("app/public/{$packageDir}");

        // Clean previous package if exists
        if (is_dir($fullPath)) {
            $this->deleteDirectory($fullPath);
        }

        mkdir($fullPath, 0755, true);

        $zip = new ZipArchive();
        $zip->open($tempPath);
        $zip->extractTo($fullPath);
        $zip->close();

        return [
            'version'     => $manifest['version'],
            'entry_point' => $manifest['entry_point'],
            'package_path' => $packageDir,
        ];
    }

    /**
     * Parse imsmanifest.xml to get SCORM version and launch URL.
     */
    private function parseManifest(string $xml): array
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        // Determine version from schema
        $version = '1.2'; // default
        $schemaVersion = $doc->getElementsByTagName('schemaversion');
        if ($schemaVersion->length > 0) {
            $ver = trim($schemaVersion->item(0)->textContent);
            if (str_contains($ver, '2004') || str_contains($ver, '1.3')) {
                $version = '2004';
            }
        }

        // Find launch resource
        $entryPoint = 'index.html';
        $resources = $doc->getElementsByTagName('resource');
        foreach ($resources as $resource) {
            $type = $resource->getAttribute('type');
            if (str_contains($type, 'sco') || $resource->getAttribute('adlcp:scormtype') === 'sco' ||
                $resource->getAttribute('adlcp:scormType') === 'sco') {
                $href = $resource->getAttribute('href');
                if ($href) {
                    $entryPoint = $href;
                    break;
                }
            }
        }

        // Fallback: first resource with href
        if ($entryPoint === 'index.html' && $resources->length > 0) {
            foreach ($resources as $resource) {
                $href = $resource->getAttribute('href');
                if ($href) {
                    $entryPoint = $href;
                    break;
                }
            }
        }

        return [
            'version'     => $version,
            'entry_point' => $entryPoint,
        ];
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
