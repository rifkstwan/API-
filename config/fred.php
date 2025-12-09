<?php

return [
    'api_key' => env('FRED_API_KEY'),
    'api_url' => env('FRED_API_URL', 'https://api.stlouisfed.org/fred'),

    // FRED Series IDs
    'series' => [
        // Economic Indicators
        'gdp' => 'GDP',
        'inflation' => 'CPIAUCSL',
        'unemployment' => 'UNRATE',
        'consumer_confidence' => 'UMCSENT',

        // Interest Rates
        'federal_funds_rate' => 'FEDFUNDS',
        'treasury_10year' => 'DGS10',
        'mortgage_30year' => 'MORTGAGE30US',
        'prime_rate' => 'DPRIME',

        // Market Indicators
        'sp500' => 'SP500',
        'dollar_index' => 'DTWEXBGS',
        'gold_price' => 'GOLDAMGBD228NLBM',
        'oil_price' => 'DCOILWTICO',
    ],
];
