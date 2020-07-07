<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Parser\Chainable;

use Nelmio\Alice\Parser\Chainable\JsonParser;
use Psr\Container\ContainerInterface;

class JsonParserFactory
{
    /*
        <service id="nelmio_alice.file_parser.chainable.json" class="Nelmio\Alice\Parser\Chainable\JsonParser">
            <tag name="nelmio_alice.file_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): JsonParser
    {
        return new JsonParser();
    }
}
