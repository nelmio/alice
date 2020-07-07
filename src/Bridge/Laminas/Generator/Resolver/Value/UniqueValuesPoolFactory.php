<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value;

use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Psr\Container\ContainerInterface;

class UniqueValuesPoolFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.unique_values_pool"
                 class="Nelmio\Alice\Generator\Resolver\UniqueValuesPool" />
    */
    public function __invoke(ContainerInterface $container): UniqueValuesPool
    {
        return new UniqueValuesPool();
    }
}
