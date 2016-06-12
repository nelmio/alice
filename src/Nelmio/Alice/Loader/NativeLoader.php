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
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\RangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FixtureBagDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ExtendFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\TemplateFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Parser\Chainable\PhpParser;
use Nelmio\Alice\FixtureBuilder\Parser\Chainable\YamlParser;
use Nelmio\Alice\FixtureBuilder\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\FixtureBuilder\Parser\ParserRegistry;
use Nelmio\Alice\FixtureBuilder\Parser\RuntimeCacheParser;
use Nelmio\Alice\FixtureBuilder\ParserInterface;
use Nelmio\Alice\FixtureBuilder\SimpleBuilder;
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

    public function getBuiltInParser(): ParserInterface
    {
        $registry = new ParserRegistry([
            new YamlParser(new SymfonyYamlParser()),
            new PhpParser(),
        ]);

        return new RuntimeCacheParser($registry, new DefaultIncludeProcessor(new DefaultFileLocator()));
    }

    public function getBuiltInBuilder(): FixtureBuilderInterface
    {
        return new SimpleBuilder(
            $this->getBuiltInParser(),
            $this->getBuiltInDenormalizer()
        );
    }

    public function getBuiltInDenormalizer(): DenormalizerInterface
    {
        return new SimpleDenormalizer(
            new SimpleParameterBagDenormalizer(),
            $this->getBuiltInFixtureBagDenormalizer()
        );
    }

    public function getBuiltInFlagParser(): FlagParserInterface
    {
        $registry = new FlagParserRegistry([
            new ExtendFlagParser(),
            new OptionalFlagParser(),
            new TemplateFlagParser(),
            new UniqueFlagParser(),
        ]);

        return new ElementFlagParser($registry);
    }

    public function getBuiltInFixtureBagDenormalizer(): FixtureBagDenormalizerInterface
    {
        return new SimpleFixtureBagDenormalizer(
            $this->getBuiltInFixtureDenormalizer(),
            $this->getBuiltInFlagParser()
        );
    }

    public function getBuiltInFixtureDenormalizer(): FixtureDenormalizerInterface
    {
        return new FixtureDenormalizerRegistry(
            $this->getBuiltInFlagParser(),
            [
                new \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer(
                    new SimpleSpecificationsDenormalizer()
                ),
                new ListNameDenormalizer(),
                new RangeNameDenormalizer(),
            ]
        );
    }
}