<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

final class GenerationContext
{
    /**
     * @var bool
     */
    private $isFirstPass;

    public function __construct()
    {
        $this->isFirstPass = true;
    }

    public function isFirstPass(): bool
    {
        return $this->isFirstPass;
    }

    public function setToSecondPass()
    {
        $this->isFirstPass = false;
    }
}
