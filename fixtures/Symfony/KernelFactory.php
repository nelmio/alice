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

namespace Nelmio\Alice\Symfony;

use function bin2hex;
use Nelmio\Alice\Bridge\Symfony\Application\AppKernel;
use function random_bytes;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelFactory
{
    public static $environments = [];

    public static function createKernel(
        string $config = null,
        string $kernelClass = AppKernel::class,
        string $environment = 'test',
        $debug = true
    ): KernelInterface {
        if (null !== $config) {
            if (false === array_key_exists($config, static::$environments)) {
                static::$environments[$config] = bin2hex(random_bytes(8));
            }

            $environment = static::$environments[$config];
        }

        $kernel = new $kernelClass($environment, $debug);
        /** @var AppKernel $kernel */
        if (null !== $config) {
            $kernel->setConfigurationResource($config);
        }

        return $kernel;
    }
}
