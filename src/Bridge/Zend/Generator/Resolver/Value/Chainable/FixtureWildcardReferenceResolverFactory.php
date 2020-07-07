<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver;
use Psr\Container\ContainerInterface;

class FixtureWildcardReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.fixture_wildcard_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver">
            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FixtureWildcardReferenceResolver
    {
        return new FixtureWildcardReferenceResolver();
    }
}
