<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizationService
{
    /**
     * @return array{contents: string, extension: string, mime: string}
     */
    public function optimize(UploadedFile $upload, int $maxWidth, int $maxHeight, int $quality = 82): array
    {
        $contents = $upload->get();

        if ($contents === false || $contents === '') {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        $image = @imagecreatefromstring($contents);

        if (! $image instanceof \GdImage) {
            return $this->original($upload, $contents);
        }

        $image = $this->applyOrientation($upload, $image);
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $requiresResize = $scale < 1;

        if ($requiresResize) {
            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
            imagedestroy($image);
            $image = $canvas;
        }

        imagesavealpha($image, true);
        ob_start();
        $encoded = imagewebp($image, null, $quality) ? ob_get_clean() : false;
        imagedestroy($image);

        if ($encoded === false || $encoded === '') {
            return $this->original($upload, $contents);
        }

        if (! $requiresResize && strlen($encoded) >= strlen($contents)) {
            return $this->original($upload, $contents);
        }

        return [
            'contents' => $encoded,
            'extension' => 'webp',
            'mime' => 'image/webp',
        ];
    }

    /**
     * @param  array{contents: string, extension: string, mime: string}  $optimized
     */
    public function store(array $optimized, string $directory, string $disk = 'public'): string
    {
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$optimized['extension'];

        if (! Storage::disk($disk)->put($path, $optimized['contents'])) {
            throw new RuntimeException('The optimized image could not be stored.');
        }

        return $path;
    }

    private function applyOrientation(UploadedFile $upload, \GdImage $image): \GdImage
    {
        if (! function_exists('exif_read_data') || $upload->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $path = $upload->getRealPath();
        $exif = $path === false ? false : @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof \GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * @return array{contents: string, extension: string, mime: string}
     */
    private function original(UploadedFile $upload, string $contents): array
    {
        $extension = Str::lower($upload->extension() ?: 'bin');

        return [
            'contents' => $contents,
            'extension' => $extension,
            'mime' => $upload->getMimeType() ?: 'application/octet-stream',
        ];
    }
}
