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

namespace Nelmio\Alice\Bridge\Symfony;

use Nelmio\Alice\Bridge\Symfony\DependencyInjection\Compiler\RegisterFakerProvidersPass;
use Nelmio\Alice\Bridge\Symfony\DependencyInjection\Compiler\RegisterTagServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NelmioAliceBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterFakerProvidersPass());
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.file_parser.registry',
                'nelmio_alice.file_parser'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.fixture_builder.denormalizer.flag_parser.registry',
                'nelmio_alice.fixture_builder.denormalizer.chainable_flag_parser'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.fixture_builder.denormalizer.fixture.registry_denormalizer',
                'nelmio_alice.fixture_builder.denormalizer.chainable_fixture_denormalizer'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.fixture_builder.denormalizer.fixture.specs.calls.simple_denormalizer',
                'nelmio_alice.fixture_builder.denormalizer.chainable_method_flag_handler'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.fixture_builder.expression_language.parser.token_parser.registry',
                'nelmio_alice.fixture_builder.expression_language.chainable_token_parser'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.generator.instantiator.registry',
                'nelmio_alice.generator.instantiator.chainable_instantiator'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.generator.caller.registry',
                'nelmio_alice.generator.caller.chainable_call_processor'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.generator.resolver.parameter.registry',
                'nelmio_alice.generator.resolver.parameter.chainable_resolver'
            )
        );
        $container->addCompilerPass(
            new RegisterTagServicesPass(
                'nelmio_alice.generator.resolver.value.registry',
                'nelmio_alice.generator.resolver.value.chainable_resolver'
            )
        );
    }
}
