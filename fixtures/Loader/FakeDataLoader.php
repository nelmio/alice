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
use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ObjectSet;

class FakeDataLoader implements DataLoaderInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
