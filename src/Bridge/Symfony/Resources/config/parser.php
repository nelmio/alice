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

use Nelmio\Alice\Parser\Chainable\JsonParser;
use Nelmio\Alice\Parser\Chainable\PhpParser;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\ParserRegistry;
use Nelmio\Alice\Parser\RuntimeCacheParser;
use Symfony\Component\Yaml\Parser;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.file_parser',
        'nelmio_alice.file_parser.runtime_cache',
    );

    $services
        ->set(
            'nelmio_alice.file_parser.runtime_cache',
            RuntimeCacheParser::class,
        )
        ->args([
            service('nelmio_alice.file_parser.registry'),
            service('nelmio_alice.file_locator'),
            service('nelmio_alice.file_parser.default_include_processor'),
        ]);

    $services
        ->set(
            'nelmio_alice.file_parser.symfony_yaml',
            Parser::class,
        )
        ->private();

    $services
        ->set(
            'nelmio_alice.file_parser.default_include_processor',
            DefaultIncludeProcessor::class,
        )
        ->args([
            service('nelmio_alice.file_locator'),
        ]);

    $services
        ->set(
            'nelmio_alice.file_parser.registry',
            ParserRegistry::class,
        )
        ->args([
            tagged_iterator('nelmio_alice.file_parser'),
        ]);

    // Chainables
    $services
        ->set(
            'nelmio_alice.file_parser.chainable.yaml',
            YamlParser::class,
        )
        ->args([
            service('nelmio_alice.file_parser.symfony_yaml'),
        ])
        ->tag('nelmio_alice.file_parser');

    $services
        ->set(
            'nelmio_alice.file_parser.chainable.php',
            PhpParser::class,
        )
        ->tag('nelmio_alice.file_parser');

    $services
        ->set(
            'nelmio_alice.file_parser.chainable.json',
            JsonParser::class,
        )
        ->tag('nelmio_alice.file_parser');
};
