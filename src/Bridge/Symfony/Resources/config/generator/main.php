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

use Nelmio\Alice\Generator\DoublePassGenerator;
use Nelmio\Alice\Generator\ObjectGenerator\CompleteObjectGenerator;
use Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator',
        'nelmio_alice.generator.double_pass',
    );

    $services
        ->set(
            'nelmio_alice.generator.double_pass',
            DoublePassGenerator::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.fixture_set'),
            service('nelmio_alice.generator.object_generator'),
        ]);

    $services->alias(
        'nelmio_alice.generator.object_generator',
        'nelmio_alice.generator.object_generator.complete',
    );

    $services
        ->set(
            'nelmio_alice.generator.object_generator.complete',
            CompleteObjectGenerator::class,
        )
        ->args([
            service('nelmio_alice.generator.object_generator.simple'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.object_generator.simple',
            SimpleObjectGenerator::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.value'),
            service('nelmio_alice.generator.instantiator'),
            service('nelmio_alice.generator.hydrator'),
            service('nelmio_alice.generator.caller'),
        ]);
};
