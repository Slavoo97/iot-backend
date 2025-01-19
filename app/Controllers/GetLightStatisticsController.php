<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Model\Entity\LightState;
use App\Model\Services\LightStateRepository;
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
 * 	"/api/get-light-statistics",
 *
 * 	methods={
 * 		"GET"="run"
 * 	},
 *  presenter="GetLightStatistics"
 * )
 */
final class GetLightStatisticsController extends AbstractController
{
    /** @var LightStateRepository @inject */
    public LightStateRepository $lightStateRepository;

    public function run(Request $request): Response
    {
        try {

            $dailyLightStats = $this->calculateDailyLightDuration();

            return new JsonResponse(
                $this->apiResponseFormatter->formatPayload($dailyLightStats),
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

    /**
     * @throws \Exception
     */
    private function calculateDailyLightDuration()
    {
        $lightStates = $this->lightStateRepository->findFromDate((new DateTime())->modify('-7 days'));

        $dailyDurations = [];
        $previousState = null;

        foreach ($lightStates as $current) {
            $currentDate = $current->getDate()->format('d.m.Y');

            if (!isset($dailyDurations[$currentDate])) {
                $dailyDurations[$currentDate] = 0;
            }

            if ($previousState && $previousState->getState() == LightState::STATE_ON && $current->getState() == LightState::STATE_OFF) {
                $startTime = $previousState->getDate();
                $endTime = $current->getDate();
                $interval = $startTime->diff($endTime);
                $dailyDurations[$currentDate] += $interval->h * 3600 + $interval->i * 60 + $interval->s;
            }

            $previousState = $current;
        }

        return array_map(function ($seconds) {
            return round($seconds / 3600, 2); // hours ($seconds / 60 for minutes)
        }, $dailyDurations);
    }
}
