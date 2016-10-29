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

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;

final class OptionalFlag implements FlagInterface
{
    /**
     * @var int
     */
    private $percentage;

    /**
     * @param int $percentage Element of ]0;100[.
     */
    public function __construct(int $percentage)
    {
        if ($percentage < 1 || $percentage > 99) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected optional flag to be an integer element of ]0;100[. Got "%d" instead.',
                    $percentage
                )
            );
        }

        $this->percentage = $percentage;
    }

    /** Element of ]0;100[.
     * @return int
     */
    public function getPercentage(): int
    {
        return $this->percentage;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return '%?';
    }
}
