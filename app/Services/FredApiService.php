<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FredApiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('fred.api_key');
        $this->baseUrl = config('fred.api_url');
    }

    /**
     * Get series observations from FRED
     */
    public function getSeriesObservations(string $seriesId, array $params = [])
    {
        try {
            $cacheKey = "fred_series_{$seriesId}_" . md5(json_encode($params));

            return Cache::remember($cacheKey, 900, function () use ($seriesId, $params) {
                $defaultParams = [
                    'series_id' => $seriesId,
                    'api_key' => $this->apiKey,
                    'file_type' => 'json',
                    'sort_order' => 'desc',
                    'limit' => 1,
                ];

                $queryParams = array_merge($defaultParams, $params);

                $response = Http::timeout(10)->get("{$this->baseUrl}/series/observations", $queryParams);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('FRED API Error', [
                    'series_id' => $seriesId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            });
        } catch (Exception $e) {
            Log::error('FRED API Exception', [
                'series_id' => $seriesId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get multiple series data
     */
    public function getMultipleSeries(array $seriesIds, array $params = [])
    {
        $results = [];

        foreach ($seriesIds as $key => $seriesId) {
            $data = $this->getSeriesObservations($seriesId, $params);
            if ($data && isset($data['observations']) && count($data['observations']) > 0) {
                $observation = $data['observations'][0];
                $results[$key] = [
                    'series_id' => $seriesId,
                    'value' => $observation['value'],
                    'date' => $observation['date'],
                    'unit' => $this->getSeriesUnit($seriesId),
                ];
            } else {
                $results[$key] = [
                    'series_id' => $seriesId,
                    'value' => null,
                    'date' => null,
                    'unit' => null,
                    'error' => 'Data not available',
                ];
            }
        }

        return $results;
    }

    /**
     * Get unit for series
     */
    protected function getSeriesUnit(string $seriesId)
    {
        $units = [
            'GDP' => 'Billions of Dollars',
            'CPIAUCSL' => 'Index 1982-1984=100',
            'UNRATE' => 'Percent',
            'UMCSENT' => 'Index 1966:Q1=100',
            'FEDFUNDS' => 'Percent',
            'DGS10' => 'Percent',
            'MORTGAGE30US' => 'Percent',
            'DPRIME' => 'Percent',
            'SP500' => 'Index',
            'DTWEXBGS' => 'Index',
            'GOLDAMGBD228NLBM' => 'U.S. Dollars per Troy Ounce',
            'DCOILWTICO' => 'Dollars per Barrel',
        ];

        return $units[$seriesId] ?? 'N/A';
    }
}
