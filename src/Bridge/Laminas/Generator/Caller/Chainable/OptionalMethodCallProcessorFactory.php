<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Caller\Chainable;

use Nelmio\Alice\Generator\Caller\Chainable\OptionalMethodCallProcessor;
use Psr\Container\ContainerInterface;

class OptionalMethodCallProcessorFactory
{
    /*
        <service id="nelmio_alice.generator.caller.chainable.optional_method_call"
                 class="Nelmio\Alice\Generator\Caller\Chainable\OptionalMethodCallProcessor">
            <tag name="nelmio_alice.generator.caller.chainable_call_processor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): OptionalMethodCallProcessor
    {
        return new OptionalMethodCallProcessor();
    }
}
