<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            'nelmio_alice.data_loader' => 'nelmio_alice.data_loader.simple',
        ],
        'factories' => [
            'nelmio_alice.data_loader.simple' => \Nelmio\Alice\Bridge\Zend\Loader\SimpleDataLoaderFactory::class,
        ],
    ],
];
