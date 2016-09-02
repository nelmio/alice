<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Symfony;

use Nelmio\Alice\Bridge\Symfony\Application\AppKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelFactory
{
    public static function createKernel(
        string $kernelClass = AppKernel::class,
        string $environment = 'test',
        $debug = true
    ): KernelInterface
    {
        return new $kernelClass($environment, $debug);
    }
}
