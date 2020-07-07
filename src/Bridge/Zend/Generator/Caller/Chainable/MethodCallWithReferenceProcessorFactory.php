<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Caller\Chainable;

use Nelmio\Alice\Generator\Caller\Chainable\MethodCallWithReferenceProcessor;
use Psr\Container\ContainerInterface;

class MethodCallWithReferenceProcessorFactory
{
    /*
        <service id="nelmio_alice.generator.caller.chainable.method_call_with_reference"
                 class="Nelmio\Alice\Generator\Caller\Chainable\MethodCallWithReferenceProcessor">
            <tag name="nelmio_alice.generator.caller.chainable_call_processor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): MethodCallWithReferenceProcessor
    {
        return new MethodCallWithReferenceProcessor();
    }
}
