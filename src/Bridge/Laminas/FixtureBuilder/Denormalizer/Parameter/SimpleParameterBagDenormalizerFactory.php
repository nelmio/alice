<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleParameterBagDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.parameter.simple_parameter_bag_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\SimpleParameterBagDenormalizer" />
    */
    public function __invoke(ContainerInterface $container): SimpleParameterBagDenormalizer
    {
        return new SimpleParameterBagDenormalizer();
    }
}
