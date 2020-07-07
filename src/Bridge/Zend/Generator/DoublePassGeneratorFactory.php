<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator;

use Nelmio\Alice\Generator\DoublePassGenerator;
use Psr\Container\ContainerInterface;

class DoublePassGeneratorFactory
{
    /*
        <service id="nelmio_alice.generator.double_pass"
                 class="Nelmio\Alice\Generator\DoublePassGenerator">
            <argument type="service" id="nelmio_alice.generator.resolver.fixture_set" />
            <argument type="service" id="nelmio_alice.generator.object_generator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): DoublePassGenerator
    {
        return new DoublePassGenerator(
            $container->get('nelmio_alice.generator.resolver.fixture_set'),
            $container->get('nelmio_alice.generator.object_generator')
        );
    }
}
