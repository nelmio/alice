<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            "nelmio_alice.property_accessor" => 'nelmio_alice.property_accessor.std',
        ],
        'factories' => [
            'nelmio_alice.property_accessor.std' => \Nelmio\Alice\Bridge\Zend\PropertyAccessor\StdPropertyAccessorFactory::class,
            'nelmio_alice.property_accessor.reflection' => \Nelmio\Alice\Bridge\Zend\PropertyAccessor\ReflectionPropertyAccessorFactory::class,
        ],
    ],
];
