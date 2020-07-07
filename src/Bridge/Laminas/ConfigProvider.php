<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

class ConfigProvider
{
    /**
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        $configAggregator = new ConfigAggregator([
            new PhpFileProvider(
                __DIR__ . '/config/*.php'
            ),
        ]);

        return  $configAggregator->getMergedConfig();
    }
}
