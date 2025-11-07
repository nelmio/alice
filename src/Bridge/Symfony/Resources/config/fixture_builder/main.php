<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder', 'nelmio_alice.fixture_builder.simple');

    $services->set('nelmio_alice.fixture_builder.simple', \Nelmio\Alice\FixtureBuilder\SimpleBuilder::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer')]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer', 'nelmio_alice.fixture_builder.denormalizer.simple');

    $services->set('nelmio_alice.fixture_builder.denormalizer.simple', \Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer::class)
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.parameter_bag'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture_bag'),
        ]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.parameter_bag', 'nelmio_alice.fixture_builder.denormalizer.parameter.simple_parameter_bag_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.parameter.simple_parameter_bag_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer::class);
};
