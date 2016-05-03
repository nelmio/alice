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

final class ListName
{
    /**
     * @var string
     */
    private $currentValue;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $flags;

    public function __construct(string $name, string $flags, string $currentValue)
    {
        $this->name = $name;
        $this->flags = $flags;
        $this->currentValue = $currentValue;
    }

    public function getCurrentValue(): string
    {
        return $this->currentValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFlags(): string
    {
        return $this->flags;
    }
}
