<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Zend\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer;
use Psr\Container\ContainerInterface;

class SimpleValueDenormalizerFactory
{
    /*
        <service id="nelmio_alice.fixture_builder.denormalizer.fixture.specs.value.simple_value_denormalizer"
                 class="Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Value\SimpleValueDenormalizer">
            <argument type="service" id="nelmio_alice.fixture_builder.expression_language.parser" />
        </service>
    */
    public function __invoke(ContainerInterface $container): SimpleValueDenormalizer
    {
        return new SimpleValueDenormalizer(
            $container->get('nelmio_alice.fixture_builder.expression_language.parser')
        );
    }
}
