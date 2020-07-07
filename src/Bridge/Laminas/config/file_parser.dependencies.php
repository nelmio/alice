<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
            'nelmio_alice.file_parser' => 'nelmio_alice.file_parser.runtime_cache',
        ],
        'factories' => [
            'nelmio_alice.file_parser.chainable.json' => \Nelmio\Alice\Bridge\Laminas\Parser\Chainable\JsonParserFactory::class,
            'nelmio_alice.file_parser.chainable.php' => \Nelmio\Alice\Bridge\Laminas\Parser\Chainable\PhpParserFactory::class,
            'nelmio_alice.file_parser.chainable.yaml' => \Nelmio\Alice\Bridge\Laminas\Parser\Chainable\YamlParserFactory::class,
            'nelmio_alice.file_parser.default_include_processor' => \Nelmio\Alice\Bridge\Laminas\Parser\DefaultIncludeProcessorFactory::class,
            'nelmio_alice.file_parser.registry' => \Nelmio\Alice\Bridge\Laminas\Parser\ParserRegistryFactory::class,
            'nelmio_alice.file_parser.runtime_cache' => \Nelmio\Alice\Bridge\Laminas\Parser\RuntimeCacheParserFactory::class,
        ],
    ],
];
