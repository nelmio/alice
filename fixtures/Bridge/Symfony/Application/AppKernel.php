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

namespace Nelmio\Alice\Bridge\Symfony\Application;

use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @var string|null
     */
    private $config;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new NelmioAliceBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->config ?? __DIR__.'/config.yml');
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    if (str_starts_with($id, 'nelmio_alice.')) {
                        $definition->setPublic(true);
                    }
                }

                foreach ($container->getAliases() as $id => $definition) {
                    if (str_starts_with($id, 'nelmio_alice.')) {
                        $definition->setPublic(true);
                    }
                }
            }
        }, PassConfig::TYPE_OPTIMIZE);
    }

    public function setConfigurationResource(string $resource): void
    {
        $this->config = $resource;
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
