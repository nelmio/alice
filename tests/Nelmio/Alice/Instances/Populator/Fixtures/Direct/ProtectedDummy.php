<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator\Fixtures\Direct;

class ProtectedDummy
{
    /** @var string */
    public $name;

    protected function setName($name)
    {
        $this->name = $name;
    }
}
