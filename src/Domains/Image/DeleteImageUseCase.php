<?php

namespace Fenrir\ImageCdn\Domains\Image;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Fenrir\Framework\ValueObjects\RootDir;

class DeleteImageUseCase
{
    public function __construct(
        private RootDir $root_dir,
        private ImageRepository $image_repository
    ) {}
    public function execute(string $bucket, string $hash, string $image_id, string $format)
    {
        $out_dir = $this->root_dir->getRealPath() . "/public/storage/{$bucket}/{$hash}/{$image_id}";
        if (!file_exists($out_dir)) {
            return;
        }
        $directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($out_dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);        
        foreach ($directory_iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        
        @rmdir($out_dir);

        $imagem = $this->image_repository->getById($image_id);
        if (!$imagem) {
            return;
        }

        $this->image_repository->deleteById($imagem->id);        
    }
}
