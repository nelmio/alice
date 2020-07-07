<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\ObjectGenerator;

use Nelmio\Alice\Generator\ObjectGenerator\CompleteObjectGenerator;
use Psr\Container\ContainerInterface;

class CompleteObjectGeneratorFactory
{
    /*
        <service id="nelmio_alice.generator.object_generator.complete"
                 class="Nelmio\Alice\Generator\ObjectGenerator\CompleteObjectGenerator">
            <argument type="service" id="nelmio_alice.generator.object_generator.simple" />
        </service>
    */
    public function __invoke(ContainerInterface $container): CompleteObjectGenerator
    {
        return new CompleteObjectGenerator(
            $container->get('nelmio_alice.generator.object_generator.simple')
        );
    }
}
