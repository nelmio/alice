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

use Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver;
use Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.resolver.fixture_set',
        'nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects',
    );

    $services
        ->set(
            'nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects',
            RemoveConflictingObjectsResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.fixture_set.simple'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.resolver.fixture_set.simple',
            SimpleFixtureSetResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.parameter_bag'),
            service('nelmio_alice.generator.resolver.fixture_bag'),
        ]);
};
