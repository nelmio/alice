<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Faker\Provider;

use Nelmio\Alice\Faker\Provider\AliceProvider;
use Psr\Container\ContainerInterface;

class AliceProviderFactory
{
    /*
        <service id="nelmio_alice.faker.provider.alice" class="Nelmio\Alice\Faker\Provider\AliceProvider">
            <tag name="nelmio_alice.faker.provider" />
        </service>
    */
    public function __invoke(ContainerInterface $container): AliceProvider
    {
        return new AliceProvider();
    }
}
