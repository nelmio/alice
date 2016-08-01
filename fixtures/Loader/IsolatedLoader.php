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

/**
 * Make use of NativeLoader for easy usage but ensure than no state is kept between each usage, perfect for isolated
 * tests.
 */
class IsolatedLoader implements FileLoaderInterface, DataLoaderInterface
{
    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        return (new NativeLoader())->loadData($data, $parameters, $objects);
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return (new NativeLoader())->loadFile($file, $parameters, $objects);
    }
}
