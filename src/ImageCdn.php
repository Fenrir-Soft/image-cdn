<?php

namespace Fenrir\ImageCdn;

use Fenrir\Framework\PluginInterface;


class ImageCdn implements PluginInterface
{

    public function getPath(): string
    {
        return __DIR__;
    }
}
