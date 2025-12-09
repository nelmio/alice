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

use Faker\Factory;
use Faker\Generator;
use Nelmio\Alice\Faker\Provider\AliceProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->alias(Generator::class, 'nelmio_alice.faker.generator')
        ->public();

    $services
        ->set(
            'nelmio_alice.faker.generator',
            Generator::class,
        )
        ->args(['%nelmio_alice.locale%'])
        ->factory([Factory::class, 'create'])
        ->call('seed', ['%nelmio_alice.seed%']);
    // Calls to add tagged providers are made in a compiler pass

    $services
        ->set(
            'nelmio_alice.faker.provider.alice',
            AliceProvider::class,
        )
        ->tag('nelmio_alice.faker.provider');
};
