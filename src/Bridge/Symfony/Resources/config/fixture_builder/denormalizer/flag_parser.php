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

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.fixture_builder.denormalizer.flag_parser',
        'nelmio_alice.fixture_builder.denormalizer.flag_parser.element',
    );

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.element',
            ElementFlagParser::class,
        )
        ->args([
            service('nelmio_alice.fixture_builder.denormalizer.flag_parser.registry'),
        ]);

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.registry',
            FlagParserRegistry::class,
        )
        ->args([
            tagged_iterator('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser'),
        ]);

    // Chainables
    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.configurator',
            ConfiguratorFlagParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.extend',
            ExtendFlagParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.optional',
            OptionalFlagParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.template',
            TemplateFlagParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services
        ->set(
            'nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.unique',
            UniqueFlagParser::class,
        )
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');
};
