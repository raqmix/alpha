<?php

namespace App\Helper;

use Exception;
use Imagick;
use Illuminate\Support\Facades\Storage;


class RemoveBgHelper
{
    public static function removeBackground($path, $disk = 'public', $outputDir = null)
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                throw new Exception("File not found at path: {$path}");
            }

            $absolutePath = Storage::disk($disk)->path($path);
            $image = ImageHelper::manager()->read($absolutePath);

            if (!($image->core()->native() instanceof Imagick)) {
                throw new Exception('Imagick driver not being used. Check your Intervention Image configuration.');
            }

            $imagick = $image->core()->native();
            $imagick->setImageFormat('png');
            $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

            $backgroundColor = $imagick->getImagePixelColor(0, 0);

            $imagick->transparentPaintImage(
                $backgroundColor,
                0,
                1000,
                false
            );

            $imagick->mergeImageLayers(Imagick::LAYERMETHOD_OPTIMIZE);

            $filename = preg_replace('/\.[a-z]+$/i', '.png', basename($path));
            $newPath = $outputDir ? rtrim($outputDir, '/') . '/' . $filename : preg_replace('/\.[a-z]+$/i', '.png', $path);

            Storage::disk($disk)->put($newPath, $imagick->getImageBlob());

            return [
                'new_path' => $newPath,
                'url' => Storage::disk($disk)->url($newPath),
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'imagick_installed' => extension_loaded('imagick'),
                'absolute_path' => isset($absolutePath) ? $absolutePath : null,
                'intervention_driver' => config('image.driver', 'gd'),
            ];
        }
    }
}
