<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver;
use Psr\Container\ContainerInterface;

class UnresolvedFixtureReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.unresolved_fixture_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver" />

            <tag name="nelmio_alice.generator.resolver.value.chainable_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): UnresolvedFixtureReferenceIdResolver
    {
        return new UnresolvedFixtureReferenceIdResolver(
            $container->get('nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver')
        );
    }
}
