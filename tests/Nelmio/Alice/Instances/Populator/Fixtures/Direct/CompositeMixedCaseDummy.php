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

class CompositeMixedCaseDummy
{
    /** @var string */
    public $fullname;

    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    }

    public function setfull_name($full_name)
    {
        // Do not set anything
    }
}
