<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->alias('nelmio_alice.data_loader', 'nelmio_alice.data_loader.simple')
        ->public();

    $services->set('nelmio_alice.data_loader.simple', \Nelmio\Alice\Loader\SimpleDataLoader::class)
        ->args([
            service('nelmio_alice.fixture_builder'),
            service('nelmio_alice.generator'),
        ]);

    $services->alias('nelmio_alice.file_loader', 'nelmio_alice.file_loader.simple')
        ->public();

    $services->set('nelmio_alice.file_loader.simple', \Nelmio\Alice\Loader\SimpleFileLoader::class)
        ->args([
            service('nelmio_alice.file_parser'),
            service('nelmio_alice.data_loader'),
        ]);

    $services->alias('nelmio_alice.files_loader', 'nelmio_alice.files_loader.simple')
        ->public();

    $services->set('nelmio_alice.files_loader.simple', \Nelmio\Alice\Loader\SimpleFilesLoader::class)
        ->args([
            service('nelmio_alice.file_parser'),
            service('nelmio_alice.data_loader'),
        ]);
};
