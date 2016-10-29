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
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Symfony\KernelIsolatedServiceCall;

/**
 * Make use of NativeLoader for easy usage but ensure than no state is kept between each usage, perfect for isolated
 * tests.
 */
class IsolatedSymfonyLoader implements FileLoaderInterface, DataLoaderInterface
{
    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        return KernelIsolatedServiceCall::call(
            'nelmio_alice.data_loader',
            function (DataLoaderInterface $loader) use ($data, $parameters, $objects) {
                return $loader->loadData($data, $parameters, $objects);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return KernelIsolatedServiceCall::call(
            'nelmio_alice.file_loader',
            function (FileLoaderInterface $loader) use ($file, $parameters, $objects) {
                return $loader->loadFile($file, $parameters, $objects);
            }
        );
    }
}
