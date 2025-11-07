<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('nelmio_alice.generator.named_arguments_resolver', \Nelmio\Alice\Generator\NamedArgumentsResolver::class);
};
