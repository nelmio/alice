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

namespace Nelmio\Alice\Entity;

class DummyWithThrowableSetter
{
    private $val = null;

    /**
     * @param $val
     */
    public function setVal($val)
    {
        if (!empty($this->val)) {
            throw new \LogicException('val is already initialised, cant be set again');
        }

        $this->val = $val;
    }
}