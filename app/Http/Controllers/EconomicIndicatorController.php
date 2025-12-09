<?php

namespace App\Http\Controllers;

use App\Services\FredApiService;
use App\Services\DataProcessorService;
use Illuminate\Http\Request;

class EconomicIndicatorController extends Controller
{
    protected $fredService;
    protected $processorService;

    public function __construct(FredApiService $fredService, DataProcessorService $processorService)
    {
        $this->fredService = $fredService;
        $this->processorService = $processorService;
    }

    /**
     * Get economic indicators
     * GET /api/economic-indicators
     */
    public function index(Request $request)
    {
        try {
            $seriesIds = [
                'gdp' => config('fred.series.gdp'),
                'inflation' => config('fred.series.inflation'),
                'unemployment' => config('fred.series.unemployment'),
                'consumer_confidence' => config('fred.series.consumer_confidence'),
            ];

            $data = $this->fredService->getMultipleSeries($seriesIds);
            $response = $this->processorService->formatEconomicData($data, 'Economic Indicators');

            return response()->json([
                'success' => true,
                'message' => 'Economic indicators retrieved successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve economic indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
