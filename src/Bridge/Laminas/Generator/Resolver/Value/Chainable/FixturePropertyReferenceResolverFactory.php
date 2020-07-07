<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver;
use Psr\Container\ContainerInterface;

class FixturePropertyReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.fixture_property_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver">
            <argument type="service" id="nelmio_alice.property_accessor" />

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): FixturePropertyReferenceResolver
    {
        return new FixturePropertyReferenceResolver(
            $container->get('nelmio_alice.property_accessor')
        );
    }
}
