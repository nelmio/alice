<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\Parser\Chainable;

use Nelmio\Alice\Parser\Chainable\YamlParser;
use Psr\Container\ContainerInterface;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

class YamlParserFactory
{
    /*
        <service id="nelmio_alice.file_parser.chainable.yaml" class="Nelmio\Alice\Parser\Chainable\YamlParser">
            <argument type="service" id="nelmio_alice.file_parser.symfony_yaml" />

            <tag name="nelmio_alice.file_parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): YamlParser
    {
        return new YamlParser(new SymfonyYamlParser());
    }
}
