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

use Nelmio\Alice\Generator\Caller\CallProcessorRegistry;
use Nelmio\Alice\Generator\Caller\Chainable\ConfiguratorMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\MethodCallWithReferenceProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\OptionalMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\Chainable\SimpleMethodCallProcessor;
use Nelmio\Alice\Generator\Caller\SimpleCaller;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.caller',
        'nelmio_alice.generator.caller.simple',
    );

    $services
        ->set(
            'nelmio_alice.generator.caller.simple',
            SimpleCaller::class,
        )
        ->args([
            service('nelmio_alice.generator.caller.registry'),
            service('nelmio_alice.generator.resolver.value'),
            service('nelmio_alice.generator.named_arguments_resolver'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.caller.registry',
            CallProcessorRegistry::class,
        )
        ->args([
            tagged_iterator('nelmio_alice.generator.caller.chainable_call_processor'),
        ]);

    // Chainables
    $services
        ->set(
            'nelmio_alice.generator.caller.chainable.configurator_method_call',
            ConfiguratorMethodCallProcessor::class,
        )
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services
        ->set(
            'nelmio_alice.generator.caller.chainable.method_call_with_reference',
            MethodCallWithReferenceProcessor::class,
        )
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services
        ->set(
            'nelmio_alice.generator.caller.chainable.optional_method_call',
            OptionalMethodCallProcessor::class,
        )
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');

    $services
        ->set(
            'nelmio_alice.generator.caller.chainable.simple_call',
            SimpleMethodCallProcessor::class,
        )
        ->tag('nelmio_alice.generator.caller.chainable_call_processor');
};
