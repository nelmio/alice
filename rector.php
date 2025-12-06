<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;

return RectorConfig::configure()
    ->withCache(__DIR__.'/var/mrector-cache')
    ->withPaths([
        __DIR__ . '/fixtures',
        __DIR__ . '/profiling',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__.'/fixtures/Bridge/Symfony/Application/var/')
    ->withPhpLevel(82_000)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withPreparedSets(
        phpunitCodeQuality: true,
    )
    ->withAttributesSets(
        phpunit: true,
    )
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withRules([
        StaticDataProviderClassMethodRector::class
    ])
    ->withSkip([
        AssertEqualsToSameRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class => __DIR__.'/fixtures',
        ClosureToArrowFunctionRector::class,
        FinalizeTestCaseClassRector::class => [
            __DIR__.'/tests/FixtureBuilder/ExpressionLanguage/Lexer/LexerIntegrationTest.php',
            __DIR__.'/tests/FixtureBuilder/ExpressionLanguage/Parser/ParserIntegrationTest.php',
            __DIR__.'/tests/Generator/Resolver/ParameterResolverIntegrationTest.php',
            __DIR__.'/tests/Loader/LoaderIntegrationTest.php',
        ],
        GetDebugTypeRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ReadOnlyPropertyRector::class => __DIR__.'/fixtures',
        PreferPHPUnitThisCallRector::class,
        StringClassNameToClassConstantRector::class,
        YieldDataProviderRector::class,
    ]);
