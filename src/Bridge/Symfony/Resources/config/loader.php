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

use Nelmio\Alice\Loader\SimpleDataLoader;
use Nelmio\Alice\Loader\SimpleFileLoader;
use Nelmio\Alice\Loader\SimpleFilesLoader;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->alias('nelmio_alice.data_loader', 'nelmio_alice.data_loader.simple')
        ->public();

    $services
        ->set(
            'nelmio_alice.data_loader.simple',
            SimpleDataLoader::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder'),
            service('nelmio_alice.generator'),
        ]);

    $services
        ->alias('nelmio_alice.file_loader', 'nelmio_alice.file_loader.simple')
        ->public();

    $services
        ->set(
            'nelmio_alice.file_loader.simple',
            SimpleFileLoader::class,
        )
        ->args([
            service('nelmio_alice.file_parser'),
            service('nelmio_alice.data_loader'),
        ]);

    $services
        ->alias('nelmio_alice.files_loader', 'nelmio_alice.files_loader.simple')
        ->public();

    $services
        ->set(
            'nelmio_alice.files_loader.simple',
            SimpleFilesLoader::class,
        )
        ->args([
            service('nelmio_alice.file_parser'),
            service('nelmio_alice.data_loader'),
        ]);
};
