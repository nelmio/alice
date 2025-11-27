<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->alias('nelmio_alice.file_locator', 'nelmio_alice.file_locator.default');

    $services->set('nelmio_alice.file_locator.default', \Nelmio\Alice\FileLocator\DefaultFileLocator::class);
};
