<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->alias('nelmio_alice.property_accessor', 'nelmio_alice.property_accessor.std');

    $services->set('nelmio_alice.property_accessor.std', \Nelmio\Alice\PropertyAccess\StdPropertyAccessor::class)
        ->args([service('property_accessor')]);
};
