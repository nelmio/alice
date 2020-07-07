<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Parser\Chainable;

use Nelmio\Alice\Parser\Chainable\PhpParser;
use Psr\Container\ContainerInterface;

class PhpParserFactory
{
    /*
        <service id="nelmio_alice.file_parser.chainable.php" class="Nelmio\Alice\Parser\Chainable\PhpParser">
            <tag name="nelmio_alice.file_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): PhpParser
    {
        return new PhpParser();
    }
}
