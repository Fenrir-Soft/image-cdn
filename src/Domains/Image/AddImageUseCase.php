<?php

namespace Fenrir\ImageCdn\Domains\Image;

class AddImageUseCase {
    public function __construct(
        private ImageRepository $image_repository
    ) {}
    public function execute(ImageModel $image) {
        $this->image_repository->save($image);
    }
}