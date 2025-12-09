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

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry;
use Nelmio\Alice\Generator\Resolver\Parameter\RemoveConflictingParametersParameterBagResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.resolver.parameter_bag',
        'nelmio_alice.generator.resolver.parameter_bag.remove_conflicting_parameters',
    );

    $services
        ->set(
            'nelmio_alice.generator.resolver.parameter_bag.remove_conflicting_parameters',
            RemoveConflictingParametersParameterBagResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.parameter_bag.simple'),
        ]);

    $services
        ->set(
            'nelmio_alice.generator.resolver.parameter_bag.simple',
            SimpleParameterBagResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.parameter.registry'),
        ]);

    $services->set(
        'nelmio_alice.generator.resolver.parameter.registry',
        ParameterResolverRegistry::class,
        // Arguments injected via a compiler pass
    );

    // Chainables
    $services
        ->set(
            'nelmio_alice.generator.resolver.parameter.chainable.static_parameter_resolver',
            StaticParameterResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.parameter.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.parameter.chainable.array_parameter_resolver',
            ArrayParameterResolver::class,
        )
        ->tag('nelmio_alice.generator.resolver.parameter.chainable_resolver');

    $services
        ->set(
            'nelmio_alice.generator.resolver.parameter.chainable.recursive_parameter_resolver',
            RecursiveParameterResolver::class,
        )
        ->args([
            service('nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver'),
            '%nelmio_alice.loading_limit%',
        ])
        ->tag('nelmio_alice.generator.resolver.parameter.chainable_resolver');

    $services->set(
        'nelmio_alice.generator.resolver.parameter.chainable.string_parameter_resolver',
        StringParameterResolver::class,
    );
};
