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

    $services->alias('nelmio_alice.property_accessor', 'nelmio_alice.property_accessor.std');

    $services->set('nelmio_alice.property_accessor.std', \Nelmio\Alice\PropertyAccess\StdPropertyAccessor::class)
        ->args([service('property_accessor')]);
};
