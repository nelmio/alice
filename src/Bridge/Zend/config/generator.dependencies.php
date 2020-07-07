<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            'nelmio_alice.generator' => 'nelmio_alice.generator.double_pass',

            'nelmio_alice.generator.caller' => 'nelmio_alice.generator.caller.simple',

            'nelmio_alice.generator.hydrator' => 'nelmio_alice.generator.hydrator.simple',
            'nelmio_alice.generator.hydrator.property' => 'nelmio_alice.generator.hydrator.property.symfony_property_access',

            'nelmio_alice.generator.instantiator' => 'nelmio_alice.generator.instantiator.existing_instance',

            'nelmio_alice.generator.object_generator' => 'nelmio_alice.generator.object_generator.complete',

            'nelmio_alice.generator.resolver.fixture_bag' => 'nelmio_alice.generator.resolver.fixture_bag.template_bag',
            'nelmio_alice.generator.resolver.fixture_set' => 'nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects',
            'nelmio_alice.generator.resolver.parameter_bag' => 'nelmio_alice.generator.resolver.parameter_bag.remove_conflicting_parameters',
            'nelmio_alice.generator.resolver.value' => 'nelmio_alice.generator.resolver.value.registry',
        ],
        'factories' => [
            'nelmio_alice.generator.caller.chainable.configurator_method_call' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\Chainable\ConfiguratorMethodCallProcessorFactory::class,
            'nelmio_alice.generator.caller.chainable.method_call_with_reference' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\Chainable\MethodCallWithReferenceProcessorFactory::class,
            'nelmio_alice.generator.caller.chainable.optional_method_call' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\Chainable\OptionalMethodCallProcessorFactory::class,
            'nelmio_alice.generator.caller.chainable.simple_call' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\Chainable\SimpleMethodCallProcessorFactory::class,
            'nelmio_alice.generator.caller.registry' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\CallProcessorRegistryFactory::class,
            'nelmio_alice.generator.caller.simple' => \Nelmio\Alice\Bridge\Zend\Generator\Caller\SimpleCallerFactory::class,

            'nelmio_alice.generator.double_pass' => \Nelmio\Alice\Bridge\Zend\Generator\DoublePassGeneratorFactory::class,

            'nelmio_alice.generator.hydrator.property.symfony_property_access' => \Nelmio\Alice\Bridge\Zend\Generator\Property\SymfonyPropertyAccessorHydratorFactory::class,
            'nelmio_alice.generator.hydrator.simple' => \Nelmio\Alice\Bridge\Zend\Generator\Hydrator\SimpleHydratorFactory::class,

            'nelmio_alice.generator.instantiator.chainable.no_caller_method_instantiator' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable\NoCallerMethodInstantiatorFactory::class,
            'nelmio_alice.generator.instantiator.chainable.no_method_call_instantiator' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable\NoMethodCallInstantiatorFactory::class,
            'nelmio_alice.generator.instantiator.chainable.null_constructor_instantiator' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable\NullConstructorInstantiatorFactory::class,
            'nelmio_alice.generator.instantiator.chainable.static_factory_instantiator' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\Chainable\StaticFactoryInstantiatorFactory::class,
            'nelmio_alice.generator.instantiator.existing_instance' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\ExistingInstanceInstantiatorFactory::class,
            'nelmio_alice.generator.instantiator.registry' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\InstantiatorRegistryFactory::class,
            'nelmio_alice.generator.instantiator.resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Instantiator\InstantiatorResolverFactory::class,

            'nelmio_alice.generator.named_arguments_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\NamedArgumentsResolverFactory::class,

            'nelmio_alice.generator.object_generator.complete' => \Nelmio\Alice\Bridge\Zend\Generator\ObjectGenerator\CompleteObjectGeneratorFactory::class,
            'nelmio_alice.generator.object_generator.simple' => \Nelmio\Alice\Bridge\Zend\Generator\ObjectGenerator\SimpleObjectGeneratorFactory::class,

            'nelmio_alice.generator.resolver.fixture_bag.template_bag' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Fixture\TemplateFixtureBagResolverFactory::class,
            'nelmio_alice.generator.resolver.fixture_set.remove_conflicting_objects' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolverFactory::class,
            'nelmio_alice.generator.resolver.fixture_set.simple' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\FixtureSet\SimpleFixtureSetResolverFactory::class,
            'nelmio_alice.generator.resolver.parameter_bag.remove_conflicting_parameters' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\ParameterBag\RemoveConflictingParametersFactory::class,
            'nelmio_alice.generator.resolver.parameter_bag.simple' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\ParameterBag\SimpleFactory::class,
            'nelmio_alice.generator.resolver.parameter.registry' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\ParameterResolverRegistryFactory::class,
            'nelmio_alice.generator.resolver.parameter.chainable.static_parameter_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable\StaticParameterResolverFactory::class,
            'nelmio_alice.generator.resolver.parameter.chainable.array_parameter_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable\ArrayParameterResolverFactory::class,
            'nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolverFactory::class,
            'nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Parameter\Chainable\StringParameterResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.array_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\ArrayValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.dynamic_array_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\DynamicArrayValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.evaluated_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\EvaluatedValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.faker_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FakerValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.fixture_property_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.fixture_method_call_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FixtureReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.fixture_wildcard_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.function_argument_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\FunctionArgumentResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.list_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\ListValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.optional_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\OptionalValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.parameter_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\ParameterValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.php_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\PhpValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.unique_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\UniqueValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.unresolved_fixture_reference_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.value_for_current_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.chainable.variable_value_resolver' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\Chainable\VariableValueResolverFactory::class,
            'nelmio_alice.generator.resolver.value.registry' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\ValueResolverRegistryFactory::class,
            'nelmio_alice.generator.resolver.value.unique_values_pool' => \Nelmio\Alice\Bridge\Zend\Generator\Resolver\Value\UniqueValuesPoolFactory::class,
        ],
    ],
];
