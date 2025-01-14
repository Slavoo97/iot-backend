<?php

declare(strict_types=1);

namespace App\Controllers;

use Contributte\ApiRouter\ApiRoute;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Response;
use App\Utils\Responses\ExtendedJsonResponse as JsonResponse;
use Nette\Http\IResponse;

/**
 * API for generating video
 *
 * @ApiRoute(
 * 	"/api/generate-video",
 * 	methods={
 * 		"GET"="run"
 * 	},
 *  presenter="GenerateVideo",
 *  format="json"
 * )
 */
final class GenerateVideoController extends AbstractController
{
    public function run(Request $request): Response
    {
        try {
//            $fileName = $values['name'] . '.mp4';
            $fileName = 'timelapse.mp4';
            $outputDir = __DIR__ . '/../../www/upload/videos/';
            $filePath = $outputDir . $fileName;

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            // Spustenie Python skriptu
            $pythonScript = escapeshellcmd("python3 " . __DIR__ . "/../../scripts/generate_video.py " . escapeshellarg($filePath));
            exec($pythonScript, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("Generovanie videa zlyhalo.");
            }

            $videoUrl = $_SERVER['HTTP_HOST'] . '/upload/videos/' . $fileName;

            return new JsonResponse(
                $this->apiResponseFormatter->formatPayload([
                    'message' => 'Video úspešne vygenerované.',
                    'video_url' => $videoUrl
                ]),
                IResponse::S200_OK
            );

        } catch (BadRequestException $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError("400", $e->getMessage()), IResponse::S400_BadRequest);
        } catch (\Exception $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError("500", $e->getMessage()), IResponse::S500_InternalServerError);
        }
    }
}
