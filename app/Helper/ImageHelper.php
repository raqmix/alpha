<?php

namespace App\Helper;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageHelper
{
    /**
     * Build an Intervention Image manager for the given driver, defaulting to
     * the one configured in config/image.php.
     */
    public static function manager(?string $driver = null): ImageManager
    {
        $driver ??= config('image.driver', 'gd');

        return new ImageManager(
            $driver === 'imagick' ? new ImagickDriver() : new GdDriver()
        );
    }
}
