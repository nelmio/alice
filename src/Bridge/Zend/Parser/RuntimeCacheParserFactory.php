<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Parser;

use Nelmio\Alice\Parser\RuntimeCacheParser;
use Psr\Container\ContainerInterface;

class RuntimeCacheParserFactory
{
    /*
        <service id="nelmio_alice.file_parser.runtime_cache"
                 class="Nelmio\Alice\Parser\RuntimeCacheParser" >
            <argument type="service" id="nelmio_alice.file_parser.registry" />
            <argument type="service" id="nelmio_alice.file_locator" />
            <argument type="service" id="nelmio_alice.file_parser.default_include_processor" />
        </service>
    */
    public function __invoke(ContainerInterface $container): RuntimeCacheParser
    {
        return new RuntimeCacheParser(
            $container->get('nelmio_alice.file_parser.registry'),
            $container->get('nelmio_alice.file_locator'),
            $container->get('nelmio_alice.file_parser.default_include_processor')
        );
    }
}
