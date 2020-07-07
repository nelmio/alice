<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            'nelmio_alice.files_loader' => 'nelmio_alice.files_loader.simple',
        ],
        'factories' => [
            'nelmio_alice.files_loader.simple' => \Nelmio\Alice\Bridge\Laminas\Loader\SimpleFilesLoaderFactory::class,
        ],
    ],
];
