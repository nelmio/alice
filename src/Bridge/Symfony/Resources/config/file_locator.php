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

use Nelmio\Alice\FileLocator\DefaultFileLocator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.file_locator',
        'nelmio_alice.file_locator.default',
    );

    $services->set(
        'nelmio_alice.file_locator.default',
        DefaultFileLocator::class,
    );
};
