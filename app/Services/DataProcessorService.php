<?php

namespace App\Services;

class DataProcessorService
{
    /**
     * Calculate percentage change
     */
    public function calculatePercentageChange($current, $previous)
    {
        if (!$previous || $previous == 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Format economic data response
     */
    public function formatEconomicData(array $data, string $category)
    {
        $formatted = [
            'category' => $category,
            'timestamp' => now()->toIso8601String(),
            'data' => [],
        ];

        foreach ($data as $key => $item) {
            if ($item['value'] !== null && $item['value'] !== '.') {
                $formatted['data'][] = [
                    'indicator' => $this->formatIndicatorName($key),
                    'value' => is_numeric($item['value']) ? (float)$item['value'] : $item['value'],
                    'unit' => $item['unit'],
                    'date' => $item['date'],
                    'series_id' => $item['series_id'],
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format indicator name
     */
    protected function formatIndicatorName(string $key)
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Generate summary statistics
     */
    public function generateSummary(array $data)
    {
        $values = array_filter(array_column($data, 'value'), function ($value) {
            return is_numeric($value);
        });

        if (empty($values)) {
            return null;
        }

        return [
            'count' => count($values),
            'average' => round(array_sum($values) / count($values), 2),
            'min' => min($values),
            'max' => max($values),
        ];
    }
}
