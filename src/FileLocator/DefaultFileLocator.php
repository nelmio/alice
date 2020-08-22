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

namespace Nelmio\Alice\FileLocator;

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;

/**
 * Symfony DefaultFileLocator shamelessly copy/pasted to avoid a dependency to the Config component and simplified a bit for
 * this package usage.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class DefaultFileLocator implements FileLocatorInterface
{
    public function locate(string $name, string $currentPath = null): string
    {
        if ('' === $name) {
            throw FileNotFoundException::createForEmptyFile();
        }

        $file = $name;
        if (false === $this->isAbsolutePath($name)) {
            $file = (null === $currentPath) ? $name : $currentPath.DIRECTORY_SEPARATOR.$name;
        }

        if (false === $path = realpath($file)) {
            throw FileNotFoundException::createForNonExistentFile($file);
        }

        return $path;
    }

    private function isAbsolutePath(string $file): bool
    {
        return ($file[0] === '/'
            || $file[0] === '\\'
            || (
                strlen($file) > 3
                && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        );
    }
}
