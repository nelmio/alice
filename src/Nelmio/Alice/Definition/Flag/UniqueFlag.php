<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\Fixture\FlagInterface;

final class UniqueFlag implements FlagInterface
{
    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return 'unique';
    }
}
