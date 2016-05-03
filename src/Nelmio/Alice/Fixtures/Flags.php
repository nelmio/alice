<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

final class Flags
{
    /** @private */
    const LOCAL_FLAG = 'local';
    /** @private */
    const TEMPLATE_FLAG = 'template';

    /**
     * @var array
     */
    private $flags = [];

    public function __construct(array $flags)
    {
        foreach ($flags as $flag) {
            switch (true) {
                case self::LOCAL_FLAG === $flag:
                    $this->flags[self::LOCAL_FLAG] = true;
                    break;

                case self::TEMPLATE_FLAG === $flag:
                    $this->flags[self::TEMPLATE_FLAG] = true;
                    break;

                case default:
                    echo '';
            }

        }
    }

    public function isLocal(): bool
    {
        return true;
    }
}
