<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Generator;

use Nelmio\Alice\Generator\NamedArgumentsResolver;
use Psr\Container\ContainerInterface;

class NamedArgumentsResolverFactory
{
    /*
        <service id="nelmio_alice.generator.named_arguments_resolver" class="Nelmio\Alice\Generator\NamedArgumentsResolver" />
    */
    public function __invoke(ContainerInterface $container): NamedArgumentsResolver
    {
        return new NamedArgumentsResolver();
    }
}
