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

use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

interface FileLocatorInterface
{
    /**
     * @param string      $name        Name of the file to locate
     * @param string|null $currentPath Path in which the file can be found
     *
     * @throws FileNotFoundException
     *
     * @return string The full path to the file
     */
    public function locate(string $name, string $currentPath = null): string;
}
