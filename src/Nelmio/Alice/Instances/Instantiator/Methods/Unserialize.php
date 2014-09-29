<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

class Unserialize implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
    {
        return !$fixture->shouldUseConstructor() && version_compare(PHP_VERSION, '5.4', '<');
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        // unserialize hack for php <5.4
        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($fixture->getClass()), $fixture->getClass()));
    }
}
