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

namespace Nelmio\Alice\Loader;

use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\CollectionDenormalizerWithTemporaryFixture;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\ReferenceRangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleCollectionDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer as NelmioSimpleDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\FunctionDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\ConfiguratorFlagHandler;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler\OptionalFlagHandler;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\ConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\FactoryDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor\LegacyConstructorDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Property\SimplePropertyDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\PropertyDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\SimpleSpecificationsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\UniqueValueDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ValueDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\TolerantFixtureDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FixtureBagDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\ConfiguratorFlagParser;
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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ArgumentEscaper;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DynamicArrayTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedValueTokenParser;
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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\WildcardReferenceTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface as ExpressionLanguageParserInterface;
use Nelmio\Alice\FixtureBuilder\SimpleBuilder;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\Generator\Caller\CallProcessorInterface;
use Nelmio\Alice\Generator\Caller\CallProcessorRegistry;
use Nelmio\Alice\Generator\Caller\Chainable\ConfiguratorMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\MethodCallWithReferenceProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\OptionalMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\SimpleMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\SimpleCaller;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\DoublePassGenerator;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\Generator\Hydrator\SimpleHydrator;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator;
use Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator;
use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGenerator\CompleteObjectGenerator;
use Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver;
use Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver;
use Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry;
use Nelmio\Alice\Generator\Resolver\Parameter\RemoveConflictingParametersParameterBagResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ArrayValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\EvaluatedValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixturePropertyReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\ValueForCurrentValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\VariableValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\ValueResolverRegistry;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Parser\Chainable\JsonParser;
use Nelmio\Alice\Parser\Chainable\PhpParser;
use Nelmio\Alice\Parser\Chainable\YamlParser;
use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Nelmio\Alice\Parser\ParserRegistry;
use Nelmio\Alice\Parser\RuntimeCacheParser;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\PropertyAccess\StdPropertyAccessor;
use Nelmio\Alice\Throwable\Exception\BadMethodCallExceptionFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * Loader implementation made to be usable without any dependency injection for quick and easy usage. For more advanced
 * usages, use {@see \Nelmio\Alice\Loader\SimpleFileLoader} instead or implement your own loader.
 *
 * WARNING: because this class is wrapping the whole configuration, the BC break policy is not fully ensured here. Not
 * methods can be added in minor versions, which could make your application break if you are extending this class and
 * have a method with the same name.
 *
 * @method DataLoaderInterface getDataLoader()
 * @method FileLoaderInterface getFileLoader()
 * @method FilesLoaderInterface getFilesLoader()
 * @method FixtureBuilderInterface getFixtureBuilder()
 * @method GeneratorInterface getGenerator()
 * @method ParserInterface getParser()
 * @method DenormalizerInterface getDenormalizer()
 * @method FixtureBagDenormalizerInterface getFixtureBagDenormalizer
 * @method FixtureDenormalizerInterface getFixtureDenormalizer()
 * @method FlagParserInterface getFlagParser()
 * @method ConstructorDenormalizerInterface getConstructorDenormalizer()
 * @method PropertyDenormalizerInterface getPropertyDenormalizer()
 * @method CallsDenormalizerInterface getCallsDenormalizer()
 * @method ArgumentsDenormalizerInterface getArgumentsDenormalizer()
 * @method ValueDenormalizerInterface getValueDenormalizer()
 * @method ExpressionLanguageParserInterface getExpressionLanguageParser()
 * @method LexerInterface getLexer()
 * @method TokenParserInterface getExpressionLanguageTokenParser()
 * @method ObjectGeneratorInterface getObjectGenerator()
 * @method FixtureSetResolverInterface getFixtureSetResolver()
 * @method ParameterBagResolverInterface getParameterResolver()
 * @method ValueResolverInterface getValueResolver()
 * @method FakerGenerator getFakerGenerator()
 * @method InstantiatorInterface getInstantiator()
 * @method HydratorInterface getHydrator()
 * @method PropertyHydratorInterface getPropertyHydrator()
 * @method PropertyAccessorInterface getPropertyAccessor()
 * @method CallerInterface getCaller()
 * @method CallProcessorInterface getCallProcessor()
 */
class NativeLoader implements FilesLoaderInterface, FileLoaderInterface, DataLoaderInterface
{
    use IsAServiceTrait;

    /** @protected */
    const LOCALE = 'en_US';

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
     * @var FilesLoaderInterface
     */
    private $filesLoader;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    public function __construct(FakerGenerator $fakerGenerator = null)
    {
        $this->fakerGenerator = (null === $fakerGenerator) ? $this->getFakerGenerator() : $fakerGenerator;
        $this->dataLoader = $this->getDataLoader();
        $this->fileLoader = $this->getFileLoader();
        $this->filesLoader = $this->getFilesLoader();
    }

    /**
     * @inheritdoc
     */
    public function loadFiles(array $files, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->filesLoader->loadFiles($files, $parameters, $objects);
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

    protected function createDataLoader(): DataLoaderInterface
    {
        return new SimpleDataLoader(
            $this->getFixtureBuilder(),
            $this->getGenerator()
        );
    }

    protected function createFileLoader(): FileLoaderInterface
    {
        return new SimpleFileLoader(
            $this->getParser(),
            $this->dataLoader
        );
    }

    protected function createFilesLoader(): FilesLoaderInterface
    {
        return new SimpleFilesLoader(
            $this->getParser(),
            $this->dataLoader
        );
    }

    protected function createFixtureBuilder(): FixtureBuilderInterface
    {
        return new SimpleBuilder(
            $this->getDenormalizer()
        );
    }

    protected function createGenerator(): GeneratorInterface
    {
        return new DoublePassGenerator(
            $this->getFixtureSetResolver(),
            $this->getObjectGenerator()
        );
    }

    protected function createParser(): ParserInterface
    {
        $registry = new ParserRegistry([
            new YamlParser(new SymfonyYamlParser()),
            new PhpParser(),
            new JsonParser(),
        ]);

        return new RuntimeCacheParser(
            $registry,
            new DefaultFileLocator(),
            new DefaultIncludeProcessor(
                new DefaultFileLocator()
            )
        );
    }

    protected function createDenormalizer(): DenormalizerInterface
    {
        return new SimpleDenormalizer(
            new SimpleParameterBagDenormalizer(),
            $this->getFixtureBagDenormalizer()
        );
    }

    protected function createFixtureBagDenormalizer(): FixtureBagDenormalizerInterface
    {
        return new SimpleFixtureBagDenormalizer(
            $this->getFixtureDenormalizer(),
            $this->getFlagParser()
        );
    }

    protected function createFixtureDenormalizer(): FixtureDenormalizerInterface
    {
        return new TolerantFixtureDenormalizer(
            new FixtureDenormalizerRegistry(
                $this->getFlagParser(),
                [
                    new NelmioSimpleDenormalizer(
                        new SimpleSpecificationsDenormalizer(
                            $this->getConstructorDenormalizer(),
                            $this->getPropertyDenormalizer(),
                            $this->getCallsDenormalizer()
                        )
                    ),
                    new SimpleCollectionDenormalizer(
                        new CollectionDenormalizerWithTemporaryFixture(
                            new NullListNameDenormalizer()
                        )
                    ),
                    new SimpleCollectionDenormalizer(
                        new CollectionDenormalizerWithTemporaryFixture(
                            new NullRangeNameDenormalizer()
                        )
                    ),
                    new ReferenceRangeNameDenormalizer(
                        new SimpleSpecificationsDenormalizer(
                            $this->getConstructorDenormalizer(),
                            $this->getPropertyDenormalizer(),
                            $this->getCallsDenormalizer()
                        )
                    )
                ]
            )
        );
    }

    protected function createFlagParser(): FlagParserInterface
    {
        $registry = new FlagParserRegistry([
            new ConfiguratorFlagParser(),
            new ExtendFlagParser(),
            new OptionalFlagParser(),
            new TemplateFlagParser(),
            new UniqueFlagParser(),
        ]);

        return new ElementFlagParser($registry);
    }

    protected function createConstructorDenormalizer(): ConstructorDenormalizerInterface
    {
        return new LegacyConstructorDenormalizer(
            new ConstructorDenormalizer(
                $this->getArgumentsDenormalizer()
            ),
            new FactoryDenormalizer(
                $this->getCallsDenormalizer()
            ),
            $this->getArgumentsDenormalizer()
        );
    }

    protected function createPropertyDenormalizer(): PropertyDenormalizerInterface
    {
        return new SimplePropertyDenormalizer(
            $this->getValueDenormalizer()
        );
    }

    protected function createCallsDenormalizer(): CallsDenormalizerInterface
    {
        return new CallsWithFlagsDenormalizer(
            new FunctionDenormalizer(
                $this->getArgumentsDenormalizer()
            ),
            [
                new ConfiguratorFlagHandler(),
                new OptionalFlagHandler(),
            ]
        );
    }

    protected function createArgumentsDenormalizer(): ArgumentsDenormalizerInterface
    {
        return new SimpleArgumentsDenormalizer(
            $this->getValueDenormalizer()
        );
    }

    protected function createValueDenormalizer(): ValueDenormalizerInterface
    {
        return new UniqueValueDenormalizer(
            new SimpleValueDenormalizer(
                $this->getExpressionLanguageParser()
            )
        );
    }

    protected function createExpressionLanguageParser(): ExpressionLanguageParserInterface
    {
        return new FunctionFixtureReferenceParser(
            new StringMergerParser(
                new SimpleParser(
                    $this->getLexer(),
                    $this->getExpressionLanguageTokenParser()
                )
            )
        );
    }

    protected function createLexer(): LexerInterface
    {
        return new EmptyValueLexer(
            new ReferenceEscaperLexer(
                new GlobalPatternsLexer(
                    new FunctionLexer(
                        new StringThenReferenceLexer(
                            new SubPatternsLexer(
                                new ReferenceLexer()
                            )
                        )
                    )
                )
            )
        );
    }

    protected function createExpressionLanguageTokenParser(): TokenParserInterface
    {
        $argumentEscaper = new ArgumentEscaper();

        return new TokenParserRegistry([
            new DynamicArrayTokenParser(),
            new EscapedValueTokenParser(),
            new FixtureListReferenceTokenParser(),
            new FixtureMethodReferenceTokenParser(),
            new FixtureRangeReferenceTokenParser(),
            new IdentityTokenParser(
                new FunctionTokenParser($argumentEscaper)
            ),
            new MethodReferenceTokenParser(),
            new OptionalTokenParser(),
            new ParameterTokenParser(),
            new PropertyReferenceTokenParser(),
            new VariableReferenceTokenParser(),
            new SimpleReferenceTokenParser(),
            new StringArrayTokenParser(),
            new StringTokenParser($argumentEscaper),
            new TolerantFunctionTokenParser(
                new IdentityTokenParser(
                    new FunctionTokenParser($argumentEscaper)
                )
            ),
            new VariableTokenParser(),
            new WildcardReferenceTokenParser(),
        ]);
    }

    protected function createObjectGenerator(): ObjectGeneratorInterface
    {
        return new CompleteObjectGenerator(
            new SimpleObjectGenerator(
                $this->getValueResolver(),
                $this->getInstantiator(),
                $this->getHydrator(),
                $this->getCaller()
            )
        );
    }

    protected function createFixtureSetResolver(): FixtureSetResolverInterface
    {
        return new RemoveConflictingObjectsResolver(
            new SimpleFixtureSetResolver(
                $this->getParameterResolver(),
                new TemplateFixtureBagResolver()
            )
        );
    }

    protected function createParameterResolver(): ParameterBagResolverInterface
    {
        $registry = new ParameterResolverRegistry([
            new StaticParameterResolver(),
            new ArrayParameterResolver(),
            new RecursiveParameterResolver(new StringParameterResolver()),
        ]);

        return new RemoveConflictingParametersParameterBagResolver(
            new SimpleParameterBagResolver($registry)
        );
    }

    protected function createValueResolver(): ValueResolverInterface
    {
        return new ValueResolverRegistry([
            new ArrayValueResolver(),
            new DynamicArrayValueResolver(),
            new EvaluatedValueResolver(),
            new FunctionCallArgumentResolver(
                new PhpFunctionCallValueResolver(
                    $this->getBlacklistedFunctions(),
                    new FakerFunctionCallValueResolver($this->fakerGenerator)
                )
            ),
            new FixturePropertyReferenceResolver(
                $this->getPropertyAccessor()
            ),
            new FixtureMethodCallReferenceResolver(),
            new UnresolvedFixtureReferenceIdResolver(
                new SelfFixtureReferenceResolver(
                    new FixtureReferenceResolver()
                )
            ),
            new FixtureWildcardReferenceResolver(),
            new ListValueResolver(),
            new OptionalValueResolver(),
            new ParameterValueResolver(),
            new UniqueValueResolver(
                new UniqueValuesPool()
            ),
            new ValueForCurrentValueResolver(),
            new VariableValueResolver(),
        ]);
    }

    /**
     * @return string[]
     */
    protected function getBlacklistedFunctions(): array
    {
        return [
            'current',
        ];
    }

    protected function createFakerGenerator(): FakerGenerator
    {
        $generator = FakerGeneratorFactory::create(static::LOCALE);
        $generator->addProvider(new AliceProvider());
        $generator->seed($this->getSeed());

        return $generator;
    }

    protected function createInstantiator(): InstantiatorInterface
    {
        return new ExistingInstanceInstantiator(
            new InstantiatorResolver(
                new InstantiatorRegistry([
                    new NoCallerMethodCallInstantiator(),
                    new NullConstructorInstantiator(),
                    new NoMethodCallInstantiator(),
                    new StaticFactoryInstantiator(),
                ])
            )
        );
    }

    protected function createHydrator(): HydratorInterface
    {
        return new SimpleHydrator(
            $this->getPropertyHydrator()
        );
    }

    protected function createPropertyHydrator(): PropertyHydratorInterface
    {
        return new SymfonyPropertyAccessorHydrator(
            $this->getPropertyAccessor()
        );
    }

    protected function createPropertyAccessor(): PropertyAccessorInterface
    {
        return new StdPropertyAccessor(
            PropertyAccess::createPropertyAccessorBuilder()
                ->enableMagicCall()
                ->getPropertyAccessor()
        );
    }

    protected function createCaller(): CallerInterface
    {
        return new SimpleCaller($this->getCallProcessor(), $this->getValueResolver());
    }

    protected function createCallProcessor(): CallProcessorInterface
    {
        return new CallProcessorRegistry([
            new ConfiguratorMethodCallProcessor(),
            new MethodCallWithReferenceProcessor(),
            new OptionalMethodCallProcessor(),
            new SimpleMethodCallProcessor(),
        ]);
    }

    /**
     * Seed used to generate random data. The seed is passed to the random number generator, so calling the a script
     * twice with the same seed produces the same results.
     *
     * @return int|null
     */
    protected function getSeed()
    {
        return 1;
    }

    public function __call(string $method, array $arguments)
    {
        if (array_key_exists($method, $this->cache)) {
            return $this->cache[$method];
        }

        if (false === preg_match('/^get.*/', $method)) {
            throw BadMethodCallExceptionFactory::createForUnknownMethod($method);
        }

        $realMethod = str_replace('get', 'create', $method);
        if ($realMethod === $this->previous) {
            throw BadMethodCallExceptionFactory::createForUnknownMethod($method);
        }

        $this->previous = $realMethod;

        $service = $this->$realMethod(...$arguments);
        $this->cache[$method] = $service;

        return $service;
    }
}
