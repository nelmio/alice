<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver;
use Psr\Container\ContainerInterface;

class FixtureReferenceResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver" />
    */
    public function __invoke(ContainerInterface $container): FixtureReferenceResolver
    {
        return new FixtureReferenceResolver();
    }
}
