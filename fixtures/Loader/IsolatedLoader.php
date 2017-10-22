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

/**
 * Make use of NativeLoader for easy usage but ensure than no state is kept between each usage, perfect for isolated
 * tests.
 */
class IsolatedLoader implements FilesLoaderInterface, FileLoaderInterface, DataLoaderInterface
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
    public function loadFiles(array $files, array $parameters = [], array $objects = []): ObjectSet
    {
        return (new NativeLoader())->loadFiles($files, $parameters, $objects);
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        return (new NativeLoader())->loadFile($file, $parameters, $objects);
    }
}
