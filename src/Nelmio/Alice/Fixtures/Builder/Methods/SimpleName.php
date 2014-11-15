<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;

class SimpleName implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canBuild($name)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        return [new Fixture($class, $name, $spec, null)];
    }
}
