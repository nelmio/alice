<?php

declare(strict_types=1);

return [
    'nelmio_alice' => [
        'locale'                  => \Faker\Factory::DEFAULT_LOCALE,
        'seed'                    => null,
        'loading_limit'           => 5,
        'max_unique_values_retry' => 150,
        'functions_blacklist'     => [
            'current',
        ],
    ],
];
