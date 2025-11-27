<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.generator.instantiator', 'nelmio_alice.generator.instantiator.existing_instance');

    $services->set('nelmio_alice.generator.instantiator.existing_instance', \Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator::class)
        ->args([service('nelmio_alice.generator.instantiator.resolver')]);

    $services->set('nelmio_alice.generator.instantiator.resolver', \Nelmio\Alice\Generator\Instantiator\InstantiatorResolver::class)
        ->args([service('nelmio_alice.generator.instantiator.registry')]);

    $services->set('nelmio_alice.generator.instantiator.registry', \Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry::class);

    $services->set('nelmio_alice.generator.instantiator.chainable.no_caller_method_instantiator', \Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator::class)
        ->args([service('nelmio_alice.generator.named_arguments_resolver')])
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services->set('nelmio_alice.generator.instantiator.chainable.null_constructor_instantiator', \Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator::class)
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services->set('nelmio_alice.generator.instantiator.chainable.no_method_call_instantiator', \Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator::class)
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services->set('nelmio_alice.generator.instantiator.chainable.static_factory_instantiator', \Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator::class)
        ->args([service('nelmio_alice.generator.named_arguments_resolver')])
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');
};
