<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            "nelmio_alice.property_accessor" => 'nelmio_alice.property_accessor.std',
        ],
        'factories' => [
            'nelmio_alice.property_accessor.std' => \Nelmio\Alice\Bridge\Laminas\PropertyAccessor\StdPropertyAccessorFactory::class,
            'nelmio_alice.property_accessor.reflection' => \Nelmio\Alice\Bridge\Laminas\PropertyAccessor\ReflectionPropertyAccessorFactory::class,
        ],
    ],
];
