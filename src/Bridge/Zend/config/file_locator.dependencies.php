<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            'nelmio_alice.file_locator' => 'nelmio_alice.file_locator.default',
        ],
        'factories' => [
            'nelmio_alice.file_locator.default' => \Nelmio\Alice\Bridge\Zend\FileLocator\DefaultFileLocatorFactory::class,
        ],
    ],
];
