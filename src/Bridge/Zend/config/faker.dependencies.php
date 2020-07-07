<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'factories' => [
            'nelmio_alice.faker.generator' => \Nelmio\Alice\Bridge\Zend\Faker\GeneratorWithProvidersFactory::class,
            'nelmio_alice.faker.provider.alice' => \Nelmio\Alice\Bridge\Zend\Faker\Provider\AliceProviderFactory::class,

            \Faker\Generator::class => \Nelmio\Alice\Bridge\Zend\Faker\GeneratorFactory::class,
        ],
    ],
];
