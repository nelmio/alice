<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeValue implements ValueInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $this->__call();
    }
}
