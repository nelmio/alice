<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'factories' => [
            'nelmio_alice.faker.generator' => \Nelmio\Alice\Bridge\Laminas\Faker\GeneratorWithProvidersFactory::class,
            'nelmio_alice.faker.provider.alice' => \Nelmio\Alice\Bridge\Laminas\Faker\Provider\AliceProviderFactory::class,

            \Faker\Generator::class => \Nelmio\Alice\Bridge\Laminas\Faker\GeneratorFactory::class,
        ],
    ],
];
