<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Parser;

use Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor;
use Psr\Container\ContainerInterface;

class DefaultIncludeProcessorFactory
{
    /*
        <service id="nelmio_alice.file_parser.default_include_processor" class="Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor" >
            <argument type="service" id="nelmio_alice.file_locator" />
        </service>
    */
    public function __invoke(ContainerInterface $container): DefaultIncludeProcessor
    {
        return new DefaultIncludeProcessor(
            $container->get('nelmio_alice.file_locator')
        );
    }
}
