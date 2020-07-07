<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Caller;

use Nelmio\Alice\Generator\Caller\CallProcessorRegistry;
use Psr\Container\ContainerInterface;

class CallProcessorRegistryFactory
{
    /*
        <service id="nelmio_alice.generator.caller.registry"
                 class="Nelmio\Alice\Generator\Caller\CallProcessorRegistry">
            <!-- Injected via a compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): CallProcessorRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $callProcessors = array_map(
            [$container, 'get'],
            $aliceConfig['generator']['caller']['chainable_call_processor']
        );

        return new CallProcessorRegistry($callProcessors);
    }
}
