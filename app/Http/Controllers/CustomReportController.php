<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CustomReportController extends Controller
{
    private $apiKey;
    private $baseUrl = 'https://api.stlouisfed.org/fred/series/observations';

    // Mapping nama indicator ke series ID FRED
    private $indicatorMapping = [
        'gdp' => 'GDP',
        'inflation' => 'CPIAUCSL',
        'unemployment' => 'UNRATE',
        'consumer_confidence' => 'UMCSENT',
        'federal_funds_rate' => 'FEDFUNDS',
        'treasury_10year' => 'DGS10',
        'mortgage_30year' => 'MORTGAGE30US',
        'prime_rate' => 'DPRIME',
        'sp500' => 'SP500',
        'dollar_index' => 'DTWEXBGS',
        'gold_price' => 'GOLDAMGBD228NLBM',
        'oil_price' => 'DCOILWTICO',
    ];

    public function __construct()
    {
        $this->apiKey = env('FRED_API_KEY');
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indicators' => 'required|array|min:1',
            'indicators.*' => 'string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestedIndicators = array_map('strtolower', $request->indicators);
        $validIndicators = [];
        $results = [];

        // Convert indicator names or series IDs to FRED series IDs
        foreach ($requestedIndicators as $indicator) {
            // Check if it's a valid indicator name
            if (isset($this->indicatorMapping[$indicator])) {
                $seriesId = $this->indicatorMapping[$indicator];
                $validIndicators[] = $indicator;
            }
            // Check if it's already a series ID
            elseif (in_array(strtoupper($indicator), $this->indicatorMapping)) {
                $seriesId = strtoupper($indicator);
                $indicatorName = array_search($seriesId, $this->indicatorMapping);
                $validIndicators[] = $indicatorName;
            } else {
                continue;
            }

            // Fetch data from FRED
            $response = Http::get($this->baseUrl, [
                'series_id' => $seriesId,
                'api_key' => $this->apiKey,
                'file_type' => 'json',
                'observation_start' => $request->start_date,
                'observation_end' => $request->end_date,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $observations = $data['observations'] ?? [];

                $results[$indicator] = [
                    'series_id' => $seriesId,
                    'data' => array_map(function ($obs) {
                        return [
                            'date' => $obs['date'],
                            'value' => $obs['value'] !== '.' ? (float)$obs['value'] : null,
                        ];
                    }, $observations)
                ];
            }
        }

        if (empty($validIndicators)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid indicators found',
                'available_indicators' => array_keys($this->indicatorMapping)
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Custom report generated successfully',
            'data' => [
                'report_period' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ],
                'indicators' => $results,
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }

    public function availableIndicators()
    {
        return response()->json([
            'success' => true,
            'message' => 'Available indicators retrieved successfully',
            'data' => [
                'indicators' => array_keys($this->indicatorMapping),
                'mapping' => $this->indicatorMapping,
            ]
        ]);
    }
}
