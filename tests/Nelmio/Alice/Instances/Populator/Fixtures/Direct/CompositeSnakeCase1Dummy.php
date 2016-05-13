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

class CompositeSnakeCase1Dummy
{
    /** @var string */
    public $full_name;

    public function set_full_name($full_name)
    {
        $this->full_name = $full_name;
    }
}
