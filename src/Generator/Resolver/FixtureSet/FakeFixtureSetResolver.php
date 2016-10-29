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

namespace Nelmio\Alice\Generator\Resolver\FixtureSet;

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotCallableTrait;

class FakeFixtureSetResolver implements FixtureSetResolverInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function resolve(FixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
