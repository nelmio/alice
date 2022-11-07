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

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection;

use Nelmio\Alice\Symfony\KernelFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @coversNothing
 *
 * @group integration
 */
class ConfigureDependencyPassTest extends TestCase
{
    public function testPropertyAccessDisabled(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('NelmioAliceBundle requires framework_bundle.property_access to be enabled.');

        $kernel = KernelFactory::createKernel(
            __DIR__.'/../../../../fixtures/Bridge/Symfony/Application/config_property_access_disabled.yml',
        );
        $kernel->boot();
    }
}
