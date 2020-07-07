<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\ObjectGenerator;

use Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator;
use Psr\Container\ContainerInterface;

class SimpleObjectGeneratorFactory
{
    /*
        <service id="nelmio_alice.generator.object_generator.simple"
                 class="Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator">
            <argument type="service" id="nelmio_alice.generator.resolver.value" />
            <argument type="service" id="nelmio_alice.generator.instantiator" />
            <argument type="service" id="nelmio_alice.generator.hydrator" />
            <argument type="service" id="nelmio_alice.generator.caller" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleObjectGenerator
    {
        return new SimpleObjectGenerator(
            $container->get('nelmio_alice.generator.resolver.value'),
            $container->get('nelmio_alice.generator.instantiator'),
            $container->get('nelmio_alice.generator.hydrator'),
            $container->get('nelmio_alice.generator.caller')
        );
    }
}
