<?php

declare(strict_types=1);

return [
    'nelmio_alice' => [
        'fixture_builder' => [
            'denormalizer' => [
                'chainable_fixture_denormalizer' => [
                    'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_list',
                    'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.reference_range_name',
                    'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_range',
                    'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple',
                ],
                'chainable_flag_parser' => [
                    'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.configurator',
                    'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.extend',
                    'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.optional',
                    'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.template',
                    'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.unique',
                ],
            ],
            'expression_language' => [
                'chainable_token_parser' => [
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.dynamic_array_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.escaped_value_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_list_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_method_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.fixture_range_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.method_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.optional_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.parameter_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.property_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.simple_reference_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_array_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.string_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.tolerant_function_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.variable_token_parser',
                    'nelmio_alice.fixture_builder.expression_language.parser.token_parser.chainable.wildcard_reference_token_parser',
                ],
            ],
        ],
    ],
];
