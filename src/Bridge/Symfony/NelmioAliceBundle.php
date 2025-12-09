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

use Nelmio\Alice\Bridge\Symfony\DependencyInjection\Compiler\CheckBundleDependenciesPass;
use Nelmio\Alice\Bridge\Symfony\DependencyInjection\Compiler\RegisterFakerProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NelmioAliceBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CheckBundleDependenciesPass());
        $container->addCompilerPass(new RegisterFakerProvidersPass());
    }
}
