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

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias(\Faker\Generator::class, 'nelmio_alice.faker.generator')
        ->public();

    $services->set('nelmio_alice.faker.generator', \Faker\Generator::class)
        ->args(['%nelmio_alice.locale%'])
        ->factory([\Faker\Factory::class, 'create'])
        ->call('seed', ['%nelmio_alice.seed%']);

    $services->set('nelmio_alice.faker.provider.alice', \Nelmio\Alice\Faker\Provider\AliceProvider::class)
        ->tag('nelmio_alice.faker.provider');
};
