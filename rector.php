<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/fixtures',
        __DIR__ . '/profiling',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__.'/fixtures/Bridge/Symfony/Application/var/')
    ->withPhpLevel(81_000)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => __DIR__.'/fixtures',
        ClosureToArrowFunctionRector::class,
        GetDebugTypeRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ReadOnlyPropertyRector::class => __DIR__.'/fixtures',
        StringClassNameToClassConstantRector::class,
    ]);
