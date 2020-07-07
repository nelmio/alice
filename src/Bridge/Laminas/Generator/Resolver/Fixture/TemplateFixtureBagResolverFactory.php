<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Fixture;

use Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver;
use Psr\Container\ContainerInterface;

class TemplateFixtureBagResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.fixture_bag.template_bag"
                class="Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver" />
    */
    public function __invoke(ContainerInterface $container): TemplateFixtureBagResolver
    {
        return new TemplateFixtureBagResolver();
    }
}
