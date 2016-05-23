<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FileLocator;

use Nelmio\Alice\FileLocatorInterface;

final class DummyFileLocator implements FileLocatorInterface
{
    public function locate(string $name, string $currentPath = null): string
    {
        throw new \BadMethodCallException();
    }
}
