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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\NotCallableTrait;

class FakeFixtureBagResolver implements FixtureBagResolverInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function resolve(FixtureBag $unresolvedFixtures): FixtureBag
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
