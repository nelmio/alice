<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.generator.resolver.fixture_set', 'nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects');

    $services->set('nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects', \Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver::class)
        ->args([service('nelmio_alice.generator.resolver.fixture_set.simple')]);

    $services->set('nelmio_alice.generator.resolver.fixture_set.simple', \Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver::class)
        ->args([
            service('nelmio_alice.generator.resolver.parameter_bag'),
            service('nelmio_alice.generator.resolver.fixture_bag'),
        ]);
};
