<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Bridge\Symfony\Application;

use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @var string|null
     */
    private $config;

    /**
     * @inheritdoc
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
    }


    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new NelmioAliceBundle(),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $config = (null === $this->config) ? __DIR__.'/config.yml' : $this->config;

        $loader->load($config);
    }

    public function setConfigurationResource(string $resource)
    {
        $this->config = $resource;
    }
}
