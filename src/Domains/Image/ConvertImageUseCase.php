<?php

namespace Fenrir\ImageCdn\Domains\Image;

use Exception;
use SplFileInfo;
use Intervention\Image\ImageManager;
use Fenrir\ImageCdn\Domains\Image\ImageRepository;
use Fenrir\Framework\ValueObjects\RootDir;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConvertImageUseCase
{
    public function __construct(
        private ImageManager $image_manager,
        private ImageRepository $image_repository,
        private RootDir $root_dir
    ) {}
    public function execute(string $bucket, string $hash, string $image_id, int $size, string $format): SplFileInfo
    {
        $image_path = "w{$size}.{$format}";
        $image = $this->image_repository->getById($image_id);

        if (!$image) {
            throw new NotFoundHttpException("Image not found", null, 404);
        }

        $tmp_file = new SplFileInfo(tempnam(sys_get_temp_dir(), $bucket));
        file_put_contents($tmp_file, file_get_contents($image->url));
        $img = $this->image_manager->read($tmp_file);
        $img->scale(width: $size);

        $out_dir = $this->root_dir->getRealPath() . "/public/storage/{$bucket}/{$hash}/{$image_id}";
        if (!file_exists($out_dir)) {
            mkdir($out_dir, 0777, true);
        }

        $filename = $out_dir . '/' . $image_path;
        $file = new SplFileInfo($filename);

        switch ($format) {
            case 'webp':
                $img->save($filename, 64);
                break;
            case 'avif':
                $img->save($filename, 51);
                break;
            case 'png':
                $img->save($filename);
                break;
            case 'jpg':
            case 'jpeg':
                $img->save($filename, 60);
                break;
            default:
                throw new Exception("Invalid Image Format", 400);
        }
        return $file;
    }
}
