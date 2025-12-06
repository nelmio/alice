<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/fixtures',
        __DIR__ . '/profiling',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpLevel(81_000)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
