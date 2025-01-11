<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Model\Services\ImageRepository;
use Contributte\ApiRouter\ApiRoute;
use DateTime;
use Nette\Application\Request;
use Nette\Application\Response;
use App\Utils\Responses\ExtendedJsonResponse as JsonResponse;
use Nette\Http\IResponse;


/**
 * API for logging users in
 *
 * @ApiRoute(
 * 	"/api/get-last-image",
 *
 * 	methods={
 * 		"GET"="run"
 * 	},
 *  presenter="GetLastImage"
 * )
 */
final class GetLastImageController extends AbstractController
{

    /** @var ImageRepository @inject */
    public ImageRepository $imageRepository;

    public function run(Request $request): Response
    {
        try {
            $lastImage = $this->imageRepository->findImageBy([], ['id' => 'DESC']);

            if ($lastImage === null) {
                return new JsonResponse(
                    $this->apiResponseFormatter->formatError(
                        IResponse::S404_NotFound,
                        'No image found'
                    ),
                    IResponse::S404_NotFound
                );
            }

            $imagePath = __DIR__ . '/../../www' . $lastImage->getFull();

            if (!file_exists($imagePath)) {
                throw new \Exception('Image file not found at path: ' . $imagePath);
            }

            $imageContent = file_get_contents($imagePath);
            $base64Image = base64_encode($imageContent);

            $finalResult = [
                'fileName' => $lastImage->getThumb(),
                'base64Image' => 'data:image/png;base64,' . $base64Image
            ];

            return new JsonResponse(
                $this->apiResponseFormatter->formatPayload($finalResult),
                IResponse::S200_OK
            );

        } catch (\Exception $e) {
            return new JsonResponse(
                $this->apiResponseFormatter->formatError(
                    IResponse::S500_InternalServerError,
                    $e->getMessage()
                ),
                IResponse::S500_InternalServerError
            );
        }
    }
}
