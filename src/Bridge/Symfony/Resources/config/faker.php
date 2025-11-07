<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias(\Faker\Generator::class, 'nelmio_alice.faker.generator')
        ->public();

    $services->set('nelmio_alice.faker.generator', \Faker\Generator::class)
        ->args(['%nelmio_alice.locale%'])
        ->factory([\Faker\Factory::class, 'create'])
        ->call('seed', ['%nelmio_alice.seed%']);

    $services->set('nelmio_alice.faker.provider.alice', \Nelmio\Alice\Faker\Provider\AliceProvider::class)
        ->tag('nelmio_alice.faker.provider');
};
