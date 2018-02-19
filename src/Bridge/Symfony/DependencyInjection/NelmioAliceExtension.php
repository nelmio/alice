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

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection;

use Faker\Provider\Base as BaseFakerProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @private
 */
final class NelmioAliceExtension extends Extension
{
    const SERVICES_DIR = __DIR__.'/../Resources/config';

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadConfig($configs, $container);
        $this->loadServices($container);
    }

    /**
     * Loads alice configuration and add the configuration values to the application parameters.
     *
     * @throws \InvalidArgumentException
     */
    private function loadConfig(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);

        foreach ($processedConfiguration as $key => $value) {
            $container->setParameter(
                $this->getAlias().'.'.$key,
                $value
            );
        }
    }

    /**
     * Loads all the services declarations.
     */
    private function loadServices(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(self::SERVICES_DIR));
        $finder = new Finder();

        $finder->files()->in(self::SERVICES_DIR);

        foreach ($finder as $file) {
            $loader->load(
                $file->getRelativePathname()
            );
        }

        $container->registerForAutoconfiguration(BaseFakerProvider::class)->addTag('nelmio_alice.faker.provider');
    }
}
