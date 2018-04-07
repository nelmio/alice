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

namespace Nelmio\Alice\Definition;

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
     * @var int
     */
    private $step;

    public function __construct(string $name, int $from, int $to, int $step = 1)
    {
        if ($from > $to) {
            list($to, $from) = [$from, $to];
        }

        $this->name = $name;
        $this->from = $from;
        $this->to = $to;
        $this->step = $step;
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

    public function getStep(): int
    {
        return $this->step;
    }
}
