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

/**
 * Value objects representing an ranged fixture name.
 *
 * @example
 *  'user{0..10}
 *
 * @internal
 */
final class RangeName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $from;

    /**
     * @var int
     */
    private $to;

    /**
     * @param string $name Range name (name without the {0..10} range bit
     * @param int    $from Positive integer forming the left endpoint of the interval formed with $to
     * @param int    $to   Positive integer forming the right endpoint of the interval formed with $from
     */
    public function __construct(string $name, int $from, int $to)
    {
        $this->name = $name;
        $this->from = $from;
        $this->to = $to;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }
}
