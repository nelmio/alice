<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Symfony\KernelFactory;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Make use of NativeLoader for easy usage but ensure than no state is kept between each usage, perfect for isolated
 * tests.
 */
class IsolatedSymfonyLoader implements FileLoaderInterface, DataLoaderInterface
{
    /**
     * @var string
     */
    private $kernelClass;

    public function __construct(string $kernelClass)
    {
        $this->kernelClass = $kernelClass;
    }

    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->load('nelmio_alice.data_loader', 'loadData', [$data, $parameters, $objects]);
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->load('nelmio_alice.file_loader', 'loadData', [$file, $parameters, $objects]);
    }

    private function load(string $loaderId, string $method, array $arguments): ObjectSet
    {
        $kernel = KernelFactory::createKernel($this->kernelClass);
        $kernel->boot();

        $container = $kernel->getContainer();
        $loader = $container->get($loaderId);

        $result = $loader->$method(...$arguments);

        $kernel->shutdown();
        if ($container instanceof ResettableContainerInterface) {
            $container->reset();
        }

        return $result;
    }
}
