<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Flag;

final class FlagBag
{
    /**
     * @var bool
     */
    private $isATemplate;

    /**
     * @var string[]
     */
    private $extends = [];

    /**
     * @param string[] $flags
     *
     * @example
     *  $flags = [
     *      'template',
     *      '
     *  ]
     */
    public function __construct(array $flags)
    {
        foreach ($flags as $flag) {
            switch (true) {
                case self::TEMPLATE_FLAG === $flag:
                    $this->flags[self::TEMPLATE_FLAG] = true;
                    break;

                default:
                    echo '';
            }

        }
    }

    public function isLocal(): bool
    {
        return isset($this->flags[self::LOCAL_FLAG]);
    }

    public function isTemplate(): bool
    {
        //TODO
    }
}
