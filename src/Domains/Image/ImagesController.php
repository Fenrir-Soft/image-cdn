<?php

namespace Fenrir\ImageCdn\Domains\Image;

use Fenrir\Framework\Attributes\Auth;
use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ImagesController
{
    public function __construct(
        private Request $request,
        private Response $response,
        private ConvertImageUseCase $convert_image_use_case,
        private DeleteImageUseCase $delete_images_use_case,
        private AddImageUseCase $add_image_use_case
    ) {}

    #[Route(path: "{bucket}/{hash}/{image_id}_w{size<\d+>}.{format<webp|avif|png|jpg|jpeg>}", methods: ["GET"])]
    public function index(string $bucket, string $hash, string $image_id, int $size, string $format)
    {
        try {
            //code...
            $file = $this->convert_image_use_case->execute($bucket, $hash, $image_id, $size, $format);

            switch ($format) {
                case 'webp':
                    $this->response->setContentType('image/webp');
                    break;
                case 'avif':
                    $this->response->setContentType('image/avif');
                    break;
                case 'png':
                    $this->response->setContentType('image/png');
                    break;
                case 'jpg':
                case 'jpeg':
                    $this->response->setContentType('image/jpg');
                    break;
                default:
                    # code...
                    break;
            }
            $this->response->setResource(fopen($file->getRealPath(), 'r'));
        } catch (Throwable $th) {
            $this->response->setStatusCode($th->getCode(), $th->getMessage());
        }
    }

    #[Auth(permissions: ["image:delete"])]
    #[Route(path: "{bucket}/{hash}/{image_id}.{format<webp|avif|png|jpg|jpeg>}", methods: ["DELETE"])]
    public function delete(string $bucket, string $hash, string $image_id, string $format)
    {
        try {
            $this->delete_images_use_case->execute($bucket, $hash, $image_id, $format);

            $this->response->setStatusCode(201);
        } catch (Throwable $th) {
            $this->response->setStatusCode($th->getCode(), $th->getMessage());
        }
    }

    #[Auth(permissions: ["image:write"])]
    #[Route(path: "{bucket}/{hash}/{image_id}.{format<webp|avif|png|jpg|jpeg>}", methods: ["POST"])]
    public function save($bucket, $hash, $image_id, $format)
    {
        try {

            
            if ($format === 'jpg') {
                $format = 'jpeg';
            }

            $image = new ImageModel();
            $image->id = $image_id;
            $image->bucket = $bucket;
            $image->hash = $hash;
            $image->url = $this->request->get('url', '');
            $image->caption = $this->request->get('caption', '');
            $image->type = "image/{$format}";
            $image->width = (int) $this->request->get('width', 0);
            $image->height = (int) $this->request->get('height', 0);
            $image->size = (int) $this->request->get('size', 0);

            $this->add_image_use_case->execute($image);

            $this->response->json($image);
        } catch (Throwable $th) {
            $this->response->setStatusCode(500, $th->getMessage())->json([
                'error' => $th->getMessage()
            ]);
        }
    }
}
