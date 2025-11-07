<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.fixture_builder.denormalizer.flag_parser', 'nelmio_alice.fixture_builder.denormalizer.flag_parser.element');

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.element', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser::class)
        ->args([service('nelmio_alice.fixture_builder.denormalizer.flag_parser.registry')]);

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.registry', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry::class);

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.configurator', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.extend', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.optional', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.template', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');

    $services->set('nelmio_alice.fixture_builder.denormalizer.flag_parser.chainable.unique', \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser::class)
        ->tag('nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser');
};
