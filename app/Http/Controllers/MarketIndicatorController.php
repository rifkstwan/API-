<?php

namespace App\Http\Controllers;

use App\Services\FredApiService;
use App\Services\DataProcessorService;
use Illuminate\Http\Request;

class MarketIndicatorController extends Controller
{
    protected $fredService;
    protected $processorService;

    public function __construct(FredApiService $fredService, DataProcessorService $processorService)
    {
        $this->fredService = $fredService;
        $this->processorService = $processorService;
    }

    /**
     * Get market indicators
     * GET /api/market-indicators
     */
    public function index(Request $request)
    {
        try {
            $seriesIds = [
                'sp500' => config('fred.series.sp500'),
                'dollar_index' => config('fred.series.dollar_index'),
                'gold_price' => config('fred.series.gold_price'),
                'oil_price' => config('fred.series.oil_price'),
            ];

            $data = $this->fredService->getMultipleSeries($seriesIds);
            $response = $this->processorService->formatEconomicData($data, 'Market Indicators');

            return response()->json([
                'success' => true,
                'message' => 'Market indicators retrieved successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve market indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
