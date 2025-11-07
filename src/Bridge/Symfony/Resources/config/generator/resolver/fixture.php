<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.generator.resolver.fixture_bag', 'nelmio_alice.generator.resolver.fixture_bag.template_bag');

    $services->set('nelmio_alice.generator.resolver.fixture_bag.template_bag', \Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver::class);
};
