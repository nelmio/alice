<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Faker;

use Faker\Factory;
use Faker\Generator;
use Psr\Container\ContainerInterface;

class GeneratorWithProvidersFactory
{
    /*
        <service id="nelmio_alice.faker.generator"
                 class="Faker\Generator">
            <factory class="Faker\Factory" method="create" />

            <argument>%nelmio_alice.locale%</argument>

            <call method="seed">
                <argument>%nelmio_alice.seed%</argument>
            </call>

            <!-- Calls to add tagged providers are made in a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): Generator
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $providers = array_map(
            [$container, 'get'],
            $aliceConfig['faker']['provider']
        );

        $generator = $container->get(Generator::class);

        foreach ($providers as $provider) {
            $generator->addProvider($provider);
        }

        return $generator;
    }
}
