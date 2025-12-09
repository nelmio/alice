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

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture_bag', 'nelmio_alice.fixture_builder.denormalizer.fixture.simple_fixture_bag_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.simple_fixture_bag_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer::class)
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture'),
            service('nelmio_alice.fixture_builder.denormalizer.flag_parser'),
        ]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture', 'nelmio_alice.fixture_builder.denormalizer.fixture.tolerant_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.tolerant_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\TolerantFixtureDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.flag_parser')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer::class);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_list', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_list')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_list', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_list')])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_range', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer::class);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.reference_range_name', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.specs.simple')])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.null_range')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple_range', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.temporary_range')])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.chainable.simple', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.specs')])
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer');

    $services->alias('nelmio_alice.fixture_builder.denormalizer.specs', 'nelmio_alice.fixture_builder.denormalizer.specs.simple');

    $services->set('nelmio_alice.fixture_builder.denormalizer.specs.simple', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer::class)
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.property'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls'),
        ]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor', 'nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.legacy_constructor_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.legacy_constructor_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer::class)
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer'),
            service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer'),
        ]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.factory_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.constructor.constructor_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments')]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments', 'nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments.simple_arguments_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments.simple_arguments_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value')]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value', 'nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.unique_value_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.unique_value_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.expression_language.parser')]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture.specs.property', 'nelmio_alice.fixture_builder.denormalizer.fixture.specs.property.simple_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.property.simple_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.value')]);

    $services->alias('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls', 'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.function_denormalizer', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.fixture.specs.arguments')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.configurator_flag_handler', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler');

    $services->set('nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.method_flag_handler.optional_flag_handler', \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler');
};
