<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

return [
    ImageManager::class => function () {
        if (class_exists('\\Imagick')) {
            return new ImageManager(new ImagickDriver());
        }
        return new ImageManager(new GdDriver());
    }
];
