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

use Symfony\Component\DependencyInjection\ResettableContainerInterface;

class KernelIsolatedServiceCall
{
    public static function call(string $serviceId, callable $getResult)
    {
        $kernel = KernelFactory::createKernel();
        $kernel->boot();

        $container = $kernel->getContainer();
        $service = $container->get($serviceId);

        $result = $getResult($service);

        $kernel->shutdown();
        if ($container instanceof ResettableContainerInterface) {
            $container->reset();
        }

        return $result;
    }
}
