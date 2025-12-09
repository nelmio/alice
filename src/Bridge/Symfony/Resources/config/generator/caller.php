<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias('nelmio_alice.generator.caller', 'nelmio_alice.generator.caller.simple');

    $services->set('nelmio_alice.generator.caller.simple', \Nelmio\Alice\Generator\Caller\SimpleCaller::class)
        ->args([
            service('nelmio_alice.generator.caller.registry'),
            service('nelmio_alice.generator.resolver.value'),
            service('nelmio_alice.generator.named_arguments_resolver'),
        ]);

    $services->set('nelmio_alice.generator.caller.registry', \Nelmio\Alice\Generator\Caller\CallProcessorRegistry::class);

    $services->set('nelmio_alice.generator.caller.chainable.configurator_method_call', \Nelmio\Alice\Generator\Caller\Chainable\ConfiguratorMethodCallProcessor::class)
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services->set('nelmio_alice.generator.caller.chainable.method_call_with_reference', \Nelmio\Alice\Generator\Caller\Chainable\MethodCallWithReferenceProcessor::class)
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services->set('nelmio_alice.generator.caller.chainable.optional_method_call', \Nelmio\Alice\Generator\Caller\Chainable\OptionalMethodCallProcessor::class)
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services->set('nelmio_alice.generator.caller.chainable.simple_call', \Nelmio\Alice\Generator\Caller\Chainable\SimpleMethodCallProcessor::class)
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');
};
