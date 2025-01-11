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
 * 	"/api/get-last-temperature",
 *
 * 	methods={
 * 		"POST"="run"
 * 	},
 *  presenter="GetLastTemperature"
 * )
 */
final class GetLastTemperatureController extends AbstractController
{
    /** @var TemperatureRepository @inject */
    public TemperatureRepository $temperatureRepository;

    public function run(Request $request): Response
    {
        try {
            $postData = $this->getRequestData($request);
            $values = json_decode($postData, true);
            $period = empty($values['period']) ? 'week' : $values['period'];

            $endDate = new DateTime('now');
            $startDate = clone $endDate;

            switch ($period) {
                case 'day':
                    $startDate->modify('-1 day');
                    break;
                case 'week':
                    $startDate->modify('-1 week');
                    break;
                case 'month':
                    $startDate->modify('-1 month');
                    break;
                default:
                    throw new \InvalidArgumentException("Neplatný parameter period. Povolené hodnoty: day, week, month.");
            }

            $data = $this->temperatureRepository->findBetweenDates($startDate, $endDate);

            if (empty($data)) {
                return new JsonResponse(
                    $this->apiResponseFormatter->formatError(
                        IResponse::S404_NotFound,
                        'No temperature data found for the selected period.'
                    ),
                    IResponse::S404_NotFound
                );
            }

            // Rozdelenie údajov do časových intervalov
            $groupedData = [];
            foreach ($data as $entry) {
                $dateKey = $entry->getDate()->format('Y-m-d H:i:s');
                $groupedData[$dateKey] = $entry->getTemperature();
            }

            // Výber maximálne 10 hodnôt rovnomerne rozložených v čase
            $selectedKeys = array_keys($groupedData);
            $totalEntries = count($selectedKeys);
            $step = max(1, intdiv($totalEntries, 10));

            $finalResult = [];
            for ($i = 0; $i < $totalEntries; $i += $step) {
                $key = $selectedKeys[$i];
                $finalResult[] = [
                    'date' => $key,
                    'temperature' => $groupedData[$key]
                ];
            }

            return new JsonResponse(
                $this->apiResponseFormatter->formatPayload($finalResult),
                IResponse::S200_OK
            );

        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(
                $this->apiResponseFormatter->formatError(
                    IResponse::S400_BadRequest,
                    $e->getMessage()
                ),
                IResponse::S400_BadRequest
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
