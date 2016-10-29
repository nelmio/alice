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
use Nelmio\Alice\NotCallableTrait;

final class DummyFileLocator implements FileLocatorInterface
{
    use NotCallableTrait;

    public function locate(string $name, string $currentPath = null): string
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
