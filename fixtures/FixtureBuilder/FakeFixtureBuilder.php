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

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\NotCallableTrait;

class FakeFixtureBuilder implements FixtureBuilderInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function build(array $data, array $parameters = [], array $objects = []): FixtureSet
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
