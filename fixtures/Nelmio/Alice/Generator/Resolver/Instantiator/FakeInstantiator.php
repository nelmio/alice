<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Instantiator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotCallableTrait;

class FakeInstantiator implements InstantiatorInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $this->__call();
    }
}
