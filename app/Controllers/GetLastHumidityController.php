<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Model\Services\TemperatureRepository;
use Contributte\ApiRouter\ApiRoute;
use DateTime;
use Nette\Application\Request;
use Nette\Application\Response;
use App\Utils\Responses\ExtendedJsonResponse as JsonResponse;
use Nette\Http\IResponse;
use Tracy\Debugger;


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

    /** @var TemperatureRepository @inject */
    public TemperatureRepository $weatherRepository;

	public function run(Request $request): Response
	{
        try {

			$result = [];

            // Inicializujeme dátumy pre začiatok a koniec minulého týždňa
            $endDate = new DateTime('now');
            $startDate = clone $endDate;
            $startDate->modify('-1 week');
            $data = $this->weatherRepository->findFromDate($startDate);

            foreach ($data as $entry) {
                $dateKey = $entry->getDate()->format('Y-m-d');

                if (!isset($result[$dateKey])) {
                    $result[$dateKey] = ['totalTemperature' => 0, 'count' => 0];
                }

                $result[$dateKey]['totalTemperature'] += $entry->getHumidity();
                $result[$dateKey]['count']++;
            }

            $finalResult = [];

            foreach ($result as $date => $values) {
                $averageTemperature = $values['totalTemperature'] / $values['count'];

                $finalResult[] = ['date' => $date, 'averageHumidity' => round($averageTemperature, 1)];
            }


            return new JsonResponse($this->apiResponseFormatter->formatPayload($finalResult), IResponse::S200_OK);

        } catch (\Exception $e) {
            return new JsonResponse($this->apiResponseFormatter->formatError($e->getCode(), $e->getMessage()), IResponse::S500_InternalServerError);
        }

	}
}
