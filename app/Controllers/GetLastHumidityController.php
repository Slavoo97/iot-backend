<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Model\Services\HumidityRepository;
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
 * 	"/api/get_last_humidity",
 *
 * 	methods={
 * 		"GET"="run"
 * 	},
 *  presenter="GetLastHumidity"
 * )
 */
final class GetLastHumidityController extends AbstractController
{

    /** @var HumidityRepository @inject */
    public HumidityRepository $humidityRepository;

	public function run(Request $request): Response
	{
        try {

			$result = [];

            $endDate = new DateTime('now');
            $startDate = clone $endDate;
            $startDate->modify('-1 week');
            $data = $this->humidityRepository->findFromDate($startDate);

            foreach ($data as $entry) {
                $result[] = [
                    'date' => $entry->getDate()->format('d-m-Y H:i:s'),
                    'value' => $entry->getHumidity()
                ];
            }

            return new JsonResponse($this->apiResponseFormatter->formatPayload($result), IResponse::S200_OK);

        } catch (\Exception $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError($e->getCode(), $e->getMessage()), IResponse::S500_InternalServerError);
        }

	}
}
