<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer;
use Nelmio\Alice\FixtureBuilder\SimpleBuilder;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Fixture Builder
    $services->alias(
        'nelmio_alice.fixture_builder',
        'nelmio_alice.fixture_builder.simple',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.simple',
            SimpleBuilder::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer'),
        ]);

    // Denormalizer
    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer',
        'nelmio_alice.fixture_builder.denormalizer.simple',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.simple',
            SimpleDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.parameter_bag'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture_bag'),
        ]);

    // Parameter Denormalizer
    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.parameter_bag',
        'nelmio_alice.fixture_builder.denormalizer.parameter.simple_parameter_bag_denormalizer',
    );

    $services->set(
        'nelmio_alice.fixture_builder.denormalizer.parameter.simple_parameter_bag_denormalizer',
        SimpleParameterBagDenormalizer::class,
    );
};
