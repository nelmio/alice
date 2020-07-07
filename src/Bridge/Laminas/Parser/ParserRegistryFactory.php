<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Parser;

use Nelmio\Alice\Parser\ParserRegistry;
use Psr\Container\ContainerInterface;

class ParserRegistryFactory
{
    /*
        <service id="nelmio_alice.file_parser.registry" class="Nelmio\Alice\Parser\ParserRegistry" >
            <!-- Injected via compiler pass -->
        </service>
    */
    public function __invoke(ContainerInterface $container): ParserRegistry
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        $parsers = array_map(
            [$container, 'get'],
            $aliceConfig['file_parser']
        );

        return new ParserRegistry($parsers);
    }
}
