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

    $services->alias('nelmio_alice.file_parser', 'nelmio_alice.file_parser.runtime_cache');

    $services->set('nelmio_alice.file_parser.runtime_cache', \Nelmio\Alice\Parser\RuntimeCacheParser::class)
        ->args([
            service('nelmio_alice.file_parser.registry'),
            service('nelmio_alice.file_locator'),
            service('nelmio_alice.file_parser.default_include_processor'),
        ]);

    $services->set('nelmio_alice.file_parser.symfony_yaml', \Symfony\Component\Yaml\Parser::class)
        ->private();

    $services->set('nelmio_alice.file_parser.default_include_processor', \Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor::class)
        ->args([service('nelmio_alice.file_locator')]);

    $services->set('nelmio_alice.file_parser.registry', \Nelmio\Alice\Parser\ParserRegistry::class);

    $services->set('nelmio_alice.file_parser.chainable.yaml', \Nelmio\Alice\Parser\Chainable\YamlParser::class)
        ->args([service('nelmio_alice.file_parser.symfony_yaml')])
        ->tag('nelmio_alice.file_parser');

    $services->set('nelmio_alice.file_parser.chainable.php', \Nelmio\Alice\Parser\Chainable\PhpParser::class)
        ->tag('nelmio_alice.file_parser');

    $services->set('nelmio_alice.file_parser.chainable.json', \Nelmio\Alice\Parser\Chainable\JsonParser::class)
        ->tag('nelmio_alice.file_parser');
};
