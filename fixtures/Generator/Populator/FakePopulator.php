<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Populator;

use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ObjectInterface;

class FakePopulator implements PopulatorInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function populate(ObjectInterface $object, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $this->__call();
    }
}
