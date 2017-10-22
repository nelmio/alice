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

namespace Nelmio\Alice;

use Nelmio\Alice\Throwable\LoadingThrowable;

/**
 * Main interface of the library.
 */
interface FilesLoaderInterface
{
    /**
     * Loads a collection of fixture files.
     *
     * @param string[] $files       Files to load.
     * @param array    $parameters Additional parameters to inject.
     * @param array    $objects    Additional objects to inject.
     *
     * @throws LoadingThrowable
     *
     * @return ObjectSet Contains the list of objects and parameters loaded and injected.
     */
    public function loadFiles(array $files, array $parameters = [], array $objects = []): ObjectSet;
}
