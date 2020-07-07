<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator\Resolver\FixtureSet;

use Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver;
use Psr\Container\ContainerInterface;

class RemoveConflictingObjectsResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects"
                 class="Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver">
            <argument type="service" id="nelmio_alice.generator.resolver.fixture_set.simple" />
        </service>
    */
    public function __invoke(ContainerInterface $container): RemoveConflictingObjectsResolver
    {
        return new RemoveConflictingObjectsResolver(
            $container->get('nelmio_alice.generator.resolver.fixture_set.simple')
        );
    }
}
