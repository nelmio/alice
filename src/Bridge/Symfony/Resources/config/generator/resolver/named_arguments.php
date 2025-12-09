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

use Nelmio\Alice\Generator\NamedArgumentsResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(
        'nelmio_alice.generator.named_arguments_resolver',
        NamedArgumentsResolver::class,
    );
};
