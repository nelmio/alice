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

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\ObjectSet;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NonIsolatedSymfonyLoader implements FilesLoaderInterface, FileLoaderInterface, DataLoaderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->container->get('nelmio_alice.data_loader')->loadData($data, $parameters, $objects);
    }

    /**
     * @inheritdoc
     */
    public function loadFiles(array $files, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->container->get('nelmio_alice.files_loader')->loadFiles($files, $parameters, $objects);
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return $this->container->get('nelmio_alice.file_loader')->loadFile($file, $parameters, $objects);
    }
}
