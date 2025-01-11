<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Model\Services\ImageRepository;
use App\Utils\MqttConfig;
use Contributte\ApiRouter\ApiRoute;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Response;
use App\Utils\Responses\ExtendedJsonResponse as JsonResponse;
use Nette\Http\IResponse;

/**
 * API for post image into DB
 *
 * @ApiRoute(
 * 	"/api/post-image",
 * 	methods={
 * 		"POST"="run"
 * 	},
 *  presenter="ImagePost",
 *  format="json"
 * )
 */
final class ImagePostController extends AbstractController
{
    /** @var ImageRepository @inject */
    public ImageRepository $imageRepository;

    public function run(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $values = json_decode($data, true);

            if (empty($values['image'])) {
                throw new BadRequestException("Obrázok vo formáte Base64 je povinný.", IResponse::S400_BadRequest);
            }

            $base64Image = $values['image'];

            if (strpos($base64Image, 'data:image/') === 0) {
                [$header, $base64Image] = explode(',', $base64Image);
            }

            $imageData = base64_decode($base64Image);
            if ($imageData === false) {
                throw new BadRequestException("Neplatné údaje Base64.", IResponse::S400_BadRequest);
            }

            $fileName = $values['name'];

            $directory = __DIR__ . '/../../www/upload/images/';
            $filePath = $directory . $fileName;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($filePath, $imageData);

            $this->imageRepository->createImage('/upload/images/' . $fileName, $fileName);

            return new JsonResponse(
                $this->apiResponseFormatter->formatMessage("Obrázok uložený."),
                IResponse::S200_OK
            );

        } catch (BadRequestException $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError("400", $e->getMessage()), IResponse::S400_BadRequest);
        } catch (\Exception $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError("500", $e->getMessage()), IResponse::S500_InternalServerError);
        }
    }
}
