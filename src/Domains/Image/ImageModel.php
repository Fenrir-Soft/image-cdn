<?php

namespace Fenrir\ImageCdn\Domains\Image;

use Symfony\Component\Uid\UuidV7;
use Throwable;

class ImageModel
{
    public string $id;
    public string $bucket;
    public string $hash;
    public string $url;
    public string $caption;
    public int $width;
    public int $height;
    public int $size;
    public string $type;
    public function __construct()
    {
        $this->id = (new UuidV7) . '';
    }

    public static function fromArray(mixed $data): static
    {
        $record = new static();

        $data = (array)$data;
        foreach ($data as $key => $value) {
            if (!property_exists($record, $key) || is_null($value)) {
                continue;
            }

            try {
                $record->{$key} = $value;
            } catch (Throwable $th) {
                //throw $th;
            }
        }

        return $record;
    }
}
