<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;


use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Nelmio\Alice\FixtureBuilder\Parser\Chainable\PhpParser;
use Nelmio\Alice\FixtureBuilder\Parser\Chainable\YamlParser;
use Nelmio\Alice\FixtureBuilder\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\FixtureBuilder\Parser\ParserRegistry;
use Nelmio\Alice\FixtureBuilder\Parser\RuntimeCacheParser;
use Nelmio\Alice\FixtureBuilder\ParserInterface;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\ObjectSet;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * Loader implementation made to be usable without any dependency injection for quick and easy usage. For more advanced
 * usages, use {@see Nelmio\Alice\Loader\SimpleLoader} instead or implement your own loader.
 */
final class NativeLoader implements LoaderInterface
{
    /**
     * @var FixtureBuilderInterface
     */
    private $builder;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct()
    {
        //TODO
    }

    /**
     * @inheritdoc
     */
    public function load(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        $fixtureSet = $this->builder->build($file, $parameters, $objects);

        return $this->generator->generate($fixtureSet);
    }

    public function __clone()
    {
        //TODO
    }

    private function getBuiltInParser(): ParserInterface
    {
        $parserRegistry = new ParserRegistry([
            new YamlParser(new SymfonyYamlParser()),
            new PhpParser(),
        ]);

        return new RuntimeCacheParser($parserRegistry, new DefaultIncludeProcessor(new DefaultFileLocator()));
    }
}
