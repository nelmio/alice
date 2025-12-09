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

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\TolerantFixtureDenormalizer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture_bag',
        'nelmio_alice.fixture_builder.denormalizer.fixture.simple_fixture_bag_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.simple_fixture_bag_denormalizer',
            SimpleFixtureBagDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture'),
            service('nelmio_alice.fixture_builder.denormalizer.flag_parser'),
        ]);

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture',
        'nelmio_alice.fixture_builder.denormalizer.fixture.tolerant_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.tolerant_denormalizer',
            TolerantFixtureDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer',
            FixtureDenormalizerRegistry::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.flag_parser'),
            tagged_iterator('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer'),
        ]);

    $services->set(
        'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list',
        NullListNameDenormalizer::class,
    );

    // Chainables
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_list',
            CollectionDenormalizerWithTemporaryFixture::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_list',
            SimpleCollectionDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_list'),
        ])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->set(
        'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_range',
        NullRangeNameDenormalizer::class,
    );

    // Specification Denormalizer
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.reference_range_name',
            ReferenceRangeNameDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.specs.simple'),
        ])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range',
            CollectionDenormalizerWithTemporaryFixture::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_range'),
        ]);

    // Specifications Constructors Denormalizer
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_range',
            SimpleCollectionDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range'),
        ])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple',
            SimpleDenormalizer::class,
        )
        ->args([service('nelmio_alice.fixture_builder.denormalizer.specs')])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.specs',
        'nelmio_alice.fixture_builder.denormalizer.specs.simple',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.specs.simple',
            SimpleSpecificationsDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.property'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
        ]);

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor',
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.legacy_constructor_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.legacy_constructor_denormalizer',
            LegacyConstructorDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer',
            FactoryDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
        ]);

    // Specifications Arguments Denormalizer
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer',
            ConstructorDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments'),
        ]);

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments',
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments.simple_arguments_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments.simple_arguments_denormalizer',
            SimpleArgumentsDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value'),
        ]);

    // Specifications Values Denormalizer
    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.value',
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.unique_value_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.unique_value_denormalizer',
            UniqueValueDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer',
            SimpleValueDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.expression_language.parser'),
        ]);

    // Specifications Properties Denormalizer
    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.property',
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.property.simple_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.property.simple_denormalizer',
            SimplePropertyDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value'),
        ]);

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls',
        'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer',
            CallsWithFlagsDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer'),
            tagged_iterator('nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer',
            FunctionDenormalizer::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments'),
        ]);

    //  Chainable method call handlers
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.configurator_flag_handler',
            ConfiguratorFlagHandler::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.optional_flag_handler',
            OptionalFlagHandler::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler');
};
