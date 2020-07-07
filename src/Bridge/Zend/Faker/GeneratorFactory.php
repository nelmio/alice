<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Faker;

use Faker\Factory;
use Faker\Generator;
use Psr\Container\ContainerInterface;

class GeneratorFactory
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

        $generator = Factory::create($aliceConfig['locale']);

        $generator->seed($aliceConfig['seed']);

        return $generator;
    }
}
