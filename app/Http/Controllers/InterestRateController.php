<?php

namespace App\Http\Controllers;

use App\Services\FredApiService;
use App\Services\DataProcessorService;
use Illuminate\Http\Request;

class InterestRateController extends Controller
{
    protected $fredService;
    protected $processorService;

    public function __construct(FredApiService $fredService, DataProcessorService $processorService)
    {
        $this->fredService = $fredService;
        $this->processorService = $processorService;
    }

    /**
     * Get interest rates
     * GET /api/interest-rates
     */
    public function index(Request $request)
    {
        try {
            $seriesIds = [
                'federal_funds_rate' => config('fred.series.federal_funds_rate'),
                'treasury_10year' => config('fred.series.treasury_10year'),
                'mortgage_30year' => config('fred.series.mortgage_30year'),
                'prime_rate' => config('fred.series.prime_rate'),
            ];

            $data = $this->fredService->getMultipleSeries($seriesIds);
            $response = $this->processorService->formatEconomicData($data, 'Interest Rates');

            return response()->json([
                'success' => true,
                'message' => 'Interest rates retrieved successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve interest rates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
