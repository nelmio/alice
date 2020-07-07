<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver;
use Psr\Container\ContainerInterface;

class SelfFixtureReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SelfFixtureReferenceResolver
    {
        return new SelfFixtureReferenceResolver(
            $container->get('nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver')
        );
    }
}
