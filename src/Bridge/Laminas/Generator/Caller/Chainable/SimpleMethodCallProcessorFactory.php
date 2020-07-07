<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Caller\Chainable;

use Nelmio\Alice\Generator\Caller\Chainable\SimpleMethodCallProcessor;
use Psr\Container\ContainerInterface;

class SimpleMethodCallProcessorFactory
{
    /*
        <service id="nelmio_alice.generator.caller.chainable.simple_call"
                 class="Nelmio\Alice\Generator\Caller\Chainable\SimpleMethodCallProcessor">
            <tag name="nelmio_alice.generator.caller.chainable_call_processor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleMethodCallProcessor
    {
        return new SimpleMethodCallProcessor();
    }
}
