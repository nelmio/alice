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

use Nelmio\Alice\Generator\Instantiator\Chainable\NoCallerMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NoMethodCallInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator;
use Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator;
use Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator;
use Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry;
use Nelmio\Alice\Generator\Instantiator\InstantiatorResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.instantiator',
        'nelmio_alice.generator.instantiator.existing_instance',
    );

    $services
        ->set(
            'nelmio_alice.generator.instantiator.existing_instance',
            ExistingInstanceInstantiator::class,
        )
        ->args([
            service('nelmio_alice.generator.instantiator.resolver'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.instantiator.resolver',
            InstantiatorResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.instantiator.registry'),
        ]);

    $services->set(
        'nelmio_alice.generator.instantiator.registry',
        InstantiatorRegistry::class,
        // Injected via a compiler pass
    );

    // Chainables
    $services
        ->set(
            'nelmio_alice.generator.instantiator.chainable.no_caller_method_instantiator',
            NoCallerMethodCallInstantiator::class,
        )
        ->args([
            service('nelmio_alice.generator.named_arguments_resolver'),
        ])
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services
        ->set(
            'nelmio_alice.generator.instantiator.chainable.null_constructor_instantiator',
            NullConstructorInstantiator::class,
        )
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services
        ->set(
            'nelmio_alice.generator.instantiator.chainable.no_method_call_instantiator',
            NoMethodCallInstantiator::class,
        )
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');

    $services
        ->set(
            'nelmio_alice.generator.instantiator.chainable.static_factory_instantiator',
            StaticFactoryInstantiator::class,
        )
        ->args([
            service('nelmio_alice.generator.named_arguments_resolver'),
        ])
        ->tag('nelmio_alice.generator.instantiator.chainable_instantiator');
};
