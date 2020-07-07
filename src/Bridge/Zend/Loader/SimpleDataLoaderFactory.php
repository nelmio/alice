<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Loader;

use Nelmio\Alice\Loader\SimpleDataLoader;
use Psr\Container\ContainerInterface;

class SimpleDataLoaderFactory
{
    /*
        <service id="nelmio_alice.data_loader.simple"
                 class="Nelmio\Alice\Loader\SimpleDataLoader">
            <argument type="service" id="nelmio_alice.fixture_builder" />
            <argument type="service" id="nelmio_alice.generator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleDataLoader
    {
        return new SimpleDataLoader(
            $container->get('nelmio_alice.fixture_builder'),
            $container->get('nelmio_alice.generator')
        );
    }
}
