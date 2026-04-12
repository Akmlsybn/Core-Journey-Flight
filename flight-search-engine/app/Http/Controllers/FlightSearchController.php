<?php

namespace App\Http\Controllers;

use App\Http\Requests\FlightCalendarPricesRequest;
use App\Http\Requests\FlightSearchRequest;
use App\Services\FlightSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class FlightSearchController extends Controller
{
    public function __construct(
        private readonly FlightSearchService $flightSearchService
    ) {}

    public function search(): View
    {
        $airports = $this->flightSearchService->getAirportsForForm();

        return view('flights.search', compact('airports'));
    }

    public function results(FlightSearchRequest $request): View
    {
        $criteria = $request->validated();
        $flights = $this->flightSearchService->search($criteria);
        $airports = $this->flightSearchService->getAirportsForForm();

        return view('flights.results', [
            'flights' => $flights,
            'airports' => $airports,
            'criteria' => $criteria,
        ]);
    }

    public function calendarPrices(FlightCalendarPricesRequest $request): JsonResponse
    {
        $payload = $this->flightSearchService->getCalendarPricing($request->validated());

        return response()->json($payload);
    }
}
