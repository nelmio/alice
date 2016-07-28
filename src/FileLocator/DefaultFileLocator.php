<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FileLocator;

use Nelmio\Alice\FileLocatorInterface;

/**
 * Symfony DefaultFileLocator shamelessly copy/pasted to avoid a dependency to the Config component and simplified a bit for
 * this package usage.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class DefaultFileLocator implements FileLocatorInterface
{
    /**
     * @inheritdoc
     */
    public function locate(string $name, string $currentPath = null): string
    {
        if ('' == $name) {
            throw new \InvalidArgumentException('An empty file name is not valid to be located.');
        }

        $file = $name;
        if (false === $this->isAbsolutePath($name)) {
            $file = (null === $currentPath)? $name : $currentPath.DIRECTORY_SEPARATOR.$name;
        }

        if (false === file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        return $file;
    }

    private function isAbsolutePath(string $file): bool
    {
        return ($file[0] === '/'
            || $file[0] === '\\'
            || (strlen($file) > 3
                && ctype_alpha($file[0])
                && $file[1] === ':'
                && ($file[2] === '\\' || $file[2] === '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        );
    }
}
