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
use Nelmio\Alice\NotCallableTrait;

class FakeDataLoader implements DataLoaderInterface
{
    use NotCallableTrait;

    public function loadData(array $data, array $parameters = [], array $objects = []): never
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
