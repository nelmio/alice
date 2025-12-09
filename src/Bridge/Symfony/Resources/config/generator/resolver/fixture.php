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

use Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(
        'nelmio_alice.generator.resolver.fixture_bag',
        'nelmio_alice.generator.resolver.fixture_bag.template_bag',
    );

    $services->set(
        'nelmio_alice.generator.resolver.fixture_bag.template_bag',
        TemplateFixtureBagResolver::class,
    );
};
