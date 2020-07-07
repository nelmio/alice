<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver;
use Psr\Container\ContainerInterface;

class FixtureMethodCallReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.fixture_method_call_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver">

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FixtureMethodCallReferenceResolver
    {
        return new FixtureMethodCallReferenceResolver();
    }
}
