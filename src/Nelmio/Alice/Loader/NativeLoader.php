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

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\ExpressionLanguage\Lexer\EmptyValueLexer;
use Nelmio\Alice\ExpressionLanguage\Lexer\GlobalPatternsLexer;
use Nelmio\Alice\ExpressionLanguage\Lexer\ReferenceLexer;
use Nelmio\Alice\ExpressionLanguage\Lexer\LexerRegistry;
use Nelmio\Alice\ExpressionLanguage\Lexer\SubPatternsLexer;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\DynamicArrayTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\EscapedArrayTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\EscapedTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\FunctionTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\IdentityTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\MethodReferenceTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\OptionalTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\ParameterTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\PropertyReferenceTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\SimpleReferenceTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\StringArrayTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\StringTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\Chainable\VariableTokenParser;
use Nelmio\Alice\ExpressionLanguage\Parser\SimpleParser;
use Nelmio\Alice\ExpressionLanguage\Parser\TokenParserRegistry;
use Nelmio\Alice\ExpressionLanguage\ParserInterface as ExpressionLanguageParserInterface;
use Nelmio\Alice\ExpressionLanguage\Parser\TokenParserInterface;
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
use Nelmio\Alice\FixtureBuilder\SimpleBuilder;
use Nelmio\Alice\Generator\Caller\DummyCaller;
use Nelmio\Alice\Generator\Caller\FakeCaller;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoConstructorInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\StaticCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator;
use Nelmio\Alice\Generator\Populator\DummyPopulator;
use Nelmio\Alice\Generator\Populator\FakePopulator;
use Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver;
use Nelmio\Alice\Generator\Resolver\Instantiator\FakeInstantiator;
use Nelmio\Alice\Generator\Resolver\SimpleFixtureSetResolver;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Parser\Chainable\PhpParser;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\ParserRegistry;
use Nelmio\Alice\Parser\RuntimeCacheParser;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\Resolver\Parameter\ArrayParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\ParameterBagResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry;
use Nelmio\Alice\Generator\Resolver\Parameter\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\StringParameterResolver;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\SimpleGenerator;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * Loader implementation made to be usable without any dependency injection for quick and easy usage. For more advanced
 * usages, use {@see Nelmio\Alice\Loader\SimpleFileLoader} instead or implement your own loader.
 */
final class NativeLoader implements FileLoaderInterface, DataLoaderInterface
{
    use NotClonableTrait;
    
    /**
     * @var FileLoaderInterface
     */
    private $fileLoader;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    public function __construct()
    {
        $this->dataLoader = $this->getBuiltInDataLoader();
        $this->fileLoader = new SimpleFileLoader(
            $this->getBuiltInParser(),
            $this->dataLoader
        );
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->fileLoader->loadFile($file, $parameters, $objects);
    }

    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->dataLoader->loadData($data, $parameters, $objects);
    }

    public function getBuiltInParser(): ParserInterface
    {
        $registry = new ParserRegistry([
            new YamlParser(new SymfonyYamlParser()),
            new PhpParser(),
        ]);

        return new RuntimeCacheParser($registry, new DefaultIncludeProcessor(new DefaultFileLocator()));
    }

    public function getBuiltInDataLoader(): DataLoaderInterface
    {
        return new SimpleDataLoader(
            $this->getBuiltInBuilder(),
            $this->getBuiltInGenerator()
        );
    }

    public function getBuiltInBuilder(): FixtureBuilderInterface
    {
        return new SimpleBuilder(
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

    public function getBuiltInGenerator(): GeneratorInterface
    {
        return new SimpleGenerator(
            $this->getBuiltInResolver(),
            $this->getBuiltInObjectResolver()
        );
    }

    public function getBuiltInResolver(): FixtureSetResolverInterface
    {
        return new SimpleFixtureSetResolver(
            $this->getBuiltInParameterResolver(),
            new TemplateFixtureBagResolver()
        );
    }

    public function getBuiltInParameterResolver(): ParameterBagResolverInterface
    {
        $registry = new ParameterResolverRegistry([
            new SimpleParameterResolver(),
            new ArrayParameterResolver(),
            new RecursiveParameterResolver(new StringParameterResolver()),
        ]);

        return new ParameterBagResolver($registry);
    }

    public function getBuiltInObjectResolver(): ObjectGeneratorInterface
    {
        return new SimpleObjectGenerator(
            new InstantiatorResolver(
                new FakeValueResolver(),
                new InstantiatorRegistry([
                    new NoCallerMethodCallInstantiator(),
                    new NoConstructorInstantiator(),
                    new StaticCallerMethodCallInstantiator(),
                ])
            ),
            new DummyPopulator(),
            new DummyCaller()
        );
    }

    public function getBuiltInExpressionLanguageParser(): ExpressionLanguageParserInterface
    {
        return new SimpleParser(
            $this->getBuiltInLexer(),
            $this->getBuiltInExpressionLanguageTokenParser()
        );
    }

    public function getBuiltInExpressionLanguageTokenParser(): TokenParserInterface
    {
        return new TokenParserRegistry([
            new DynamicArrayTokenParser(),
            new EscapedArrayTokenParser(),
            new EscapedTokenParser(),
            new FunctionTokenParser(),
            new IdentityTokenParser(),
            new MethodReferenceTokenParser(),
            new OptionalTokenParser(),
            new ParameterTokenParser(),
            new PropertyReferenceTokenParser(),
            new SimpleReferenceTokenParser(),
            new StringArrayTokenParser(),
            new StringTokenParser(),
            new VariableTokenParser(),
        ]);
    }

    public function getBuiltInLexer(): LexerInterface
    {
        return new LexerRegistry([
            new EmptyValueLexer(),
            new GlobalPatternsLexer(),
            new SubPatternsLexer(
                new ReferenceLexer()
            ),
        ]);
    }
}
