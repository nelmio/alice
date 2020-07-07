<?php

declare(strict_types=1);

return [
    'nelmio_alice' => [
        'generator' => [
            'caller' => [
                'chainable_call_processor' => [
                    'nelmio_alice.generator.caller.chainable.configurator_method_call',
                    'nelmio_alice.generator.caller.chainable.method_call_with_reference',
                    'nelmio_alice.generator.caller.chainable.optional_method_call',
                    'nelmio_alice.generator.caller.chainable.simple_call',
                ],
            ],
            'instantiator' => [
                'chainable_instantiator' => [
                    'nelmio_alice.generator.instantiator.chainable.no_caller_method_instantiator',
                    'nelmio_alice.generator.instantiator.chainable.null_constructor_instantiator',
                    'nelmio_alice.generator.instantiator.chainable.no_method_call_instantiator',
                    'nelmio_alice.generator.instantiator.chainable.static_factory_instantiator',
                ],
            ],
            'resolver' => [
                'parameter' => [
                    'chainable_resolver' => [
                        'nelmio_alice.generator.resolver.parameter.chainable.static_parameter_resolver',
                        'nelmio_alice.generator.resolver.parameter.chainable.array_parameter_resolver',
                        'nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver',
                    ],
                ],
                'value' => [
                    'chainable_resolver' => [
                        'nelmio_alice.generator.resolver.value.chainable.array_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.dynamic_array_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.evaluated_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.function_argument_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.fixture_property_reference_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.fixture_method_call_reference_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.unresolved_fixture_reference_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.fixture_wildcard_reference_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.list_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.optional_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.parameter_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.unique_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.value_for_current_value_resolver',
                        'nelmio_alice.generator.resolver.value.chainable.variable_value_resolver',
                    ],
                ]
            ],
        ],
    ],
];
