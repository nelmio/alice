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

    $services->alias('nelmio_alice.generator.hydrator', 'nelmio_alice.generator.hydrator.simple');

    $services->set('nelmio_alice.generator.hydrator.simple', \Nelmio\Alice\Generator\Hydrator\SimpleHydrator::class)
        ->args([service('nelmio_alice.generator.hydrator.property')]);

    $services->alias('nelmio_alice.generator.hydrator.property', 'nelmio_alice.generator.hydrator.property.symfony_property_access');

    $services->set('nelmio_alice.generator.hydrator.property.symfony_property_access', \Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator::class)
        ->args([service('nelmio_alice.property_accessor')]);
};
