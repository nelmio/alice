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

use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\LexerRegistry;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DynamicArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureListReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureMethodReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureRangeReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\MethodReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\OptionalTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\PropertyReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\SimpleReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\StringTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\TolerantFunctionTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\WildcardReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface as ExpressionLanguageParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParserInterface;
use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\RangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\OptionalCallsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorWithCallerDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\SimpleConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\PropertyDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
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
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator;
use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator;
use Nelmio\Alice\Generator\Hydrator\SimpleHydrator;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver;
use Nelmio\Alice\Generator\Resolver\SimpleFixtureSetResolver;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\Parser\Chainable\PhpParser;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\ParserRegistry;
use Nelmio\Alice\Parser\RuntimeCacheParser;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\DoublePassGenerator;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * Loader implementation made to be usable without any dependency injection for quick and easy usage. For more advanced
 * usages, use {@see Nelmio\Alice\Loader\SimpleFileLoader} instead or implement your own loader.
 *
 * @method DataLoaderInterface getBuiltInDataLoader()
 * @method FileLoaderInterface getBuiltInFileLoader()
 *
 * @method FixtureBuilderInterface getBuiltInFixtureBuilder()
 * @method GeneratorInterface getBuiltInGenerator()
 * @method ParserInterface getBuiltInParser()
 *
 * @method DenormalizerInterface getBuiltInDenormalizer()
 * @method FixtureBagDenormalizerInterface getBuiltInFixtureBagDenormalizer
 * @method FixtureDenormalizerInterface getBuiltInFixtureDenormalizer()
 * @method FlagParserInterface getBuiltInFlagParser()
 * @method ConstructorDenormalizerInterface getBuiltInConstructorDenormalizer()
 * @method PropertyDenormalizerInterface getBuiltInPropertyDenormalizer()
 * @method CallsDenormalizerInterface getBuiltInCallsDenormalizer()
 * @method ArgumentsDenormalizerInterface getBuiltInArgumentsDenormalizer()
 * @method ValueDenormalizerInterface getBuiltInValueDenormalizer()
 *
 * @method ExpressionLanguageParserInterface getBuiltInExpressionLanguageParser()
 * @method LexerInterface getBuiltInLexer()
 * @method TokenParserInterface getBuiltInExpressionLanguageTokenParser()
 *
 * @method ObjectGeneratorInterface getBuiltInObjectGenerator()
 *
 * @method FixtureSetResolverInterface getBuiltInFixtureSetResolver()
 * @method ParameterBagResolverInterface getBuiltInParameterResolver()
 * @method ValueResolverInterface getBuiltInValueResolver()
 * @method FakerGenerator getFakerGenerator()
 *
 * @method InstantiatorInterface getBuiltInInstantiator()
 * @method HydratorInterface getBuiltInHydrator()
 * @method PropertyHydratorInterface getBuiltInPropertyHydrator()
 * @method CallerInterface getBuiltInCaller()
 */
final class NativeLoader implements FileLoaderInterface, DataLoaderInterface
{
    use NotClonableTrait;

    private $previous = '';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var FakerGenerator
     */
    private $fakerGenerator;

    /**
     * @var FileLoaderInterface
     */
    private $fileLoader;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    public function __construct(FakerGenerator $fakerGenerator = null)
    {
        $this->fakerGenerator = (null === $fakerGenerator) ? $this->getFakerGenerator() : $fakerGenerator;
        $this->dataLoader = $this->getBuiltInDataLoader();
        $this->fileLoader = $this->getBuiltInFileLoader();
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

    protected function createBuiltInDataLoader(): DataLoaderInterface
    {
        return new SimpleDataLoader(
            $this->getBuiltInFixtureBuilder(),
            $this->getBuiltInGenerator()
        );
    }

    protected function createBuiltInFileLoader(): FileLoaderInterface
    {
        return new SimpleFileLoader(
            $this->getBuiltInParser(),
            $this->dataLoader
        );
    }

    protected function createBuiltInFixtureBuilder(): FixtureBuilderInterface
    {
        return new SimpleBuilder(
            $this->getBuiltInDenormalizer()
        );
    }

    protected function createBuiltInGenerator(): GeneratorInterface
    {
        return new DoublePassGenerator(
            $this->getBuiltInFixtureSetResolver(),
            $this->getBuiltInObjectGenerator()
        );
    }

    protected function createBuiltInParser(): ParserInterface
    {
        $registry = new ParserRegistry([
            new YamlParser(new SymfonyYamlParser()),
            new PhpParser(),
        ]);

        return new RuntimeCacheParser($registry, new DefaultIncludeProcessor(new DefaultFileLocator()));
    }

    protected function createBuiltInDenormalizer(): DenormalizerInterface
    {
        return new SimpleDenormalizer(
            new SimpleParameterBagDenormalizer(),
            $this->getBuiltInFixtureBagDenormalizer()
        );
    }

    protected function createBuiltInFixtureBagDenormalizer(): FixtureBagDenormalizerInterface
    {
        return new SimpleFixtureBagDenormalizer(
            $this->getBuiltInFixtureDenormalizer(),
            $this->getBuiltInFlagParser()
        );
    }

    protected function createBuiltInFixtureDenormalizer(): FixtureDenormalizerInterface
    {
        return new FixtureDenormalizerRegistry(
            $this->getBuiltInFlagParser(),
            [
                new \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer(
                    new SimpleSpecificationsDenormalizer(
                        $this->getBuiltInConstructorDenormalizer(),
                        $this->getBuiltInPropertyDenormalizer(),
                        $this->getBuiltInCallsDenormalizer()
                    )
                ),
                new ListNameDenormalizer(),
                new RangeNameDenormalizer(),
            ]
        );
    }

    protected function createBuiltInFlagParser(): FlagParserInterface
    {
        $registry = new FlagParserRegistry([
            new ExtendFlagParser(),
            new OptionalFlagParser(),
            new TemplateFlagParser(),
            new UniqueFlagParser(),
        ]);

        return new ElementFlagParser($registry);
    }

    protected function createBuiltInConstructorDenormalizer(): ConstructorDenormalizerInterface
    {
        return new ConstructorWithCallerDenormalizer(
            new SimpleConstructorDenormalizer(
                $this->getBuiltInArgumentsDenormalizer()
            )
        );
    }

    protected function createBuiltInPropertyDenormalizer(): PropertyDenormalizerInterface
    {
        return new SimplePropertyDenormalizer(
            $this->getBuiltInValueDenormalizer()
        );
    }

    protected function createBuiltInCallsDenormalizer(): CallsDenormalizerInterface
    {
        return new OptionalCallsDenormalizer(
            $this->getBuiltInArgumentsDenormalizer()
        );
    }

    protected function createBuiltInArgumentsDenormalizer(): ArgumentsDenormalizerInterface
    {
        return new SimpleArgumentsDenormalizer(
            $this->getBuiltInValueDenormalizer()
        );
    }

    protected function createBuiltInValueDenormalizer(): ValueDenormalizerInterface
    {
        return new UniqueValueDenormalizer(
            $this->getBuiltInExpressionLanguageParser()
        );
    }

    protected function createBuiltInExpressionLanguageParser(): ExpressionLanguageParserInterface
    {
        return new FunctionFixtureReferenceParser(
            new StringMergerParser(
                new SimpleParser(
                    $this->getBuiltInLexer(),
                    $this->getBuiltInExpressionLanguageTokenParser()
                )
            )
        );
    }

    protected function createBuiltInLexer(): LexerInterface
    {
        return new LexerRegistry([
            new EmptyValueLexer(),
            new GlobalPatternsLexer(),
            new SubPatternsLexer(
                new ReferenceLexer()
            ),
        ]);
    }

    protected function createBuiltInExpressionLanguageTokenParser(): TokenParserInterface
    {
        return new TokenParserRegistry([
            new DynamicArrayTokenParser(),
            new EscapedArrayTokenParser(),
            new EscapedTokenParser(),
            new FixtureListReferenceTokenParser(),
            new FixtureMethodReferenceTokenParser(),
            new FixtureRangeReferenceTokenParser(),
            new IdentityTokenParser(),
            new MethodReferenceTokenParser(),
            new OptionalTokenParser(),
            new ParameterTokenParser(),
            new PropertyReferenceTokenParser(),
            new SimpleReferenceTokenParser(),
            new StringArrayTokenParser(),
            new StringTokenParser(),
            new TolerantFunctionTokenParser(new FunctionTokenParser()),
            new VariableTokenParser(),
            new WildcardReferenceTokenParser(),
        ]);
    }

    protected function createBuiltInObjectGenerator(): ObjectGeneratorInterface
    {
        return new SimpleObjectGenerator(
            $this->getBuiltInValueResolver(),
            $this->getBuiltInInstantiator(),
            $this->getBuiltInHydrator(),
            $this->getBuiltInCaller()
        );
    }

    protected function createBuiltInFixtureSetResolver(): FixtureSetResolverInterface
    {
        return new SimpleFixtureSetResolver(
            $this->getBuiltInParameterResolver(),
            new TemplateFixtureBagResolver()
        );
    }

    protected function createBuiltInParameterResolver(): ParameterBagResolverInterface
    {
        $registry = new ParameterResolverRegistry([
            new StaticParameterResolver(),
            new ArrayParameterResolver(),
            new RecursiveParameterResolver(new StringParameterResolver()),
        ]);

        return new SimpleParameterBagResolver($registry);
    }

    protected function createBuiltInValueResolver(): ValueResolverInterface
    {
        return new ValueResolverRegistry([
            new DynamicArrayValueResolver(),
            new EvaluatedValueResolver(),
            new FakerFunctionCallValueResolver($this->fakerGenerator),
            new FixturePropertyReferenceResolver(
                $this->getPropertyAccessor()
            ),
            new UnresolvedFixtureReferenceResolver(
                new SelfFixtureReferenceResolver(
                    new FixtureReferenceResolver()
                )
            ),
            new FixtureWildcardReferenceResolver(),
            new ListValueResolver(),
            new OptionalValueResolver(),
            new UniqueValueResolver(
                new UniqueValuesPool()
            ),
            new ValueForCurrentValueResolver(),
            new VariableValueResolver(),
        ]);
    }

    protected function createFakerGenerator(): FakerGenerator
    {
        $generator = FakerGeneratorFactory::create();
        $generator->addProvider(new AliceProvider());

        return $generator;
    }

    protected function createBuiltInInstantiator(): InstantiatorInterface
    {
        return new InstantiatorResolver(
            new InstantiatorRegistry([
                new NoCallerMethodCallInstantiator(),
                new NullConstructorInstantiator(),
                new NoMethodCallInstantiator(),
                new StaticFactoryInstantiator(),
            ])
        );
    }

    protected function createBuiltInHydrator(): HydratorInterface
    {
        return new SimpleHydrator(
            $this->getBuiltInPropertyHydrator()
        );
    }

    protected function createBuiltInPropertyHydrator(): PropertyHydratorInterface
    {
        return new SymfonyPropertyAccessorHydrator(
            $this->getPropertyAccessor()
        );
    }

    protected function createPropertyAccessor(): PropertyAccessorInterface
    {
        return PropertyAccess::createPropertyAccessorBuilder()->enableMagicCall()->getPropertyAccessor();
    }

    protected function createBuiltInCaller(): CallerInterface
    {
        return new DummyCaller();
    }

    public function __call(string $method, array $arguments)
    {
        if (array_key_exists($method, $this->cache)) {
            return $this->cache[$method];
        }

        if (false === preg_match('/^get.*/', $method)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Unknown method "%s".',
                    $method
                )
            );
        }

        $realMethod = str_replace('get', 'create', $method);
        if ($realMethod === $this->previous) {
            throw new \BadMethodCallException(
                sprintf(
                    'Unknown method "%s".',
                    $method
                )
            );
        }
        $this->previous = $realMethod;

        $service = $this->$realMethod(...$arguments);
        $this->cache[$method] = $service;

        return $service;
    }
}
