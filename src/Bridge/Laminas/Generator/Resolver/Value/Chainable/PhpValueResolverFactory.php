<?php

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Laminas\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Generator\Resolver\Value\Chainable\ParameterValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver;
use Psr\Container\ContainerInterface;

class PhpValueResolverFactory
{
    /*
        <service id="nelmio_alice.generator.resolver.value.chainable.php_value_resolver"
                 class="Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver">
            <argument type="string">%nelmio_alice.functions_blacklist%</argument>
            <argument type="service" id="nelmio_alice.generator.resolver.value.chainable.faker_value_resolver" />
        </service>
    */
    public function __invoke(ContainerInterface $container): PhpFunctionCallValueResolver
    {
        $aliceConfig = $container->get('config')['nelmio_alice'];

        return new PhpFunctionCallValueResolver(
            $aliceConfig['functions_blacklist'],
            $container->get('nelmio_alice.generator.resolver.value.chainable.faker_value_resolver')
        );
    }
}
