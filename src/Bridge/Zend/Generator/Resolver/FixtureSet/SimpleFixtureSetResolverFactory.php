<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\FixtureSet;

use Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver;
use Psr\Container\ContainerInterface;

class SimpleFixtureSetResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.fixture_set.simple"
                 class="Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.parameter_bag" />
            <argument type="service" id="nelmio_alice.generator.resolver.fixture_bag" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleFixtureSetResolver
    {
        return new SimpleFixtureSetResolver(
            $container->get('nelmio_alice.generator.resolver.parameter_bag'),
            $container->get('nelmio_alice.generator.resolver.fixture_bag')
        );
    }
}
