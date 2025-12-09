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

use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ArrayValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.resolver.value',
        'nelmio_alice.generator.resolver.value.registry',
    );

    $services->set(
        'nelmio_alice.generator.resolver.value.registry',
        ValueResolverRegistry::class,
        // Injected via a compiler pass
    );

    $services->set(
        'nelmio_alice.generator.resolver.value.unique_values_pool',
        UniqueValuesPool::class,
    );

    $services->alias(
        'nelmio_alice.generator.resolver.value.property_accessor',
        'property_accessor',
    );

    // Chainables
    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.array_value_resolver',
            ArrayValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.dynamic_array_value_resolver',
            DynamicArrayValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.evaluated_value_resolver',
            EvaluatedValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.faker_value_resolver',
            FakerFunctionCallValueResolver::class,
        )
        ->args([
            service('nelmio_alice.faker.generator'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.php_value_resolver',
            PhpFunctionCallValueResolver::class,
        )
        ->args([
            '%nelmio_alice.functions_blacklist%',
            service('nelmio_alice.generator.resolver.value.chainable.faker_value_resolver'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.function_argument_resolver',
            FunctionCallArgumentResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.value.chainable.php_value_resolver'),
        ])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.fixture_property_reference_resolver',
            FixturePropertyReferenceResolver::class,
        )
        ->args([
            service('nelmio_alice.property_accessor'),
        ])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.fixture_method_call_reference_resolver',
            FixtureMethodCallReferenceResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services->set(
        'nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver',
        FixtureReferenceResolver::class,
    );

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver',
            SelfFixtureReferenceResolver::class,
        )
        ->args([service('nelmio_alice.generator.resolver.value.chainable.fixture_reference_resolver')]);

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.unresolved_fixture_reference_resolver',
            UnresolvedFixtureReferenceIdResolver::class,
        )
        ->args([service('nelmio_alice.generator.resolver.value.chainable.self_fixture_reference_resolver')])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.fixture_wildcard_reference_resolver',
            FixtureWildcardReferenceResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.list_value_resolver',
            ListValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.optional_value_resolver',
            OptionalValueResolver::class,
        )
        ->args([
            '$faker' => service('nelmio_alice.faker.generator'),
        ])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.parameter_value_resolver',
            ParameterValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.unique_value_resolver',
            UniqueValueResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.value.unique_values_pool'),
            null,
            '%nelmio_alice.max_unique_values_retry%',
        ])
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.value_for_current_value_resolver',
            ValueForCurrentValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.value.chainable.variable_value_resolver',
            VariableValueResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.value.chainable_resolver');
};
