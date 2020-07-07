<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FileLocator;

use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Psr\Container\ContainerInterface;

class DefaultFileLocatorFactory
{
    /*
        <service id="nelmio_alice.file_locator.default"
                 class="Nelmio\Alice\FileLocator\DefaultFileLocator" />
    */
    public function __invoke(ContainerInterface $container): DefaultFileLocator
    {
        return new DefaultFileLocator();
    }
}
