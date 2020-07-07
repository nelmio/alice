<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Loader;

use Nelmio\Alice\Loader\SimpleFilesLoader;
use Psr\Container\ContainerInterface;

class SimpleFilesLoaderFactory
{
    /*
        <service id="nelmio_alice.files_loader.simple"
                 class="Nelmio\Alice\Loader\SimpleFilesLoader">
            <argument type="service" id="nelmio_alice.file_parser" />
            <argument type="service" id="nelmio_alice.data_loader" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleFilesLoader
    {
        return new SimpleFilesLoader(
            $container->get('nelmio_alice.file_parser'),
            $container->get('nelmio_alice.data_loader')
        );
    }
}
