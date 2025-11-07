<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.generator.resolver.value', 'nelmio_alice.generator.resolver.value.registry');

    $services->set('nelmio_alice.generator.resolver.value.registry', \Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry::class);

    $services->set('nelmio_alice.generator.resolver.value.unique_values_pool', \Nelmio\Alice\Generator\Resolver\UniqueValuesPool::class);

    $services->alias('nelmio_alice.generator.resolver.value.property_accessor', 'property_accessor');

    $services->set('nelmio_alice.generator.resolver.value.chainable.array_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\ArrayValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.dynamic_array_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.evaluated_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.faker_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver::class)
        ->args([service('nelmio_alice.faker.generator')]);

    $services->set('nelmio_alice.generator.resolver.value.chainable.php_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver::class)
        ->args([
            '%nelmio_alice.functions_blacklist%',
            service('nelmio_alice.generator.resolver.value.chainable.faker_value_resolver'),
        ]);

    $services->set('nelmio_alice.generator.resolver.value.chainable.function_argument_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver::class)
        ->args([service('nelmio_alice.generator.resolver.value.chainable.php_value_resolver')])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.fixture_property_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver::class)
        ->args([service('nelmio_alice.property_accessor')])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.fixture_method_call_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver::class);

    $services->set('nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver::class)
        ->args([service('nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver')]);

    $services->set('nelmio_alice.generator.resolver.value.chainable.unresolved_fixture_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver::class)
        ->args([service('nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver')])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.fixture_wildcard_reference_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.list_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.optional_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver::class)
        ->args(['$faker' => service('nelmio_alice.faker.generator')])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.parameter_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.unique_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver::class)
        ->args([
            service('nelmio_alice.generator.resolver.value.unique_values_pool'),
            null,
            '%nelmio_alice.max_unique_values_retry%',
        ])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.value_for_current_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set('nelmio_alice.generator.resolver.value.chainable.variable_value_resolver', \Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver::class)
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');
};
