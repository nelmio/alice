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
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

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
        if ($percentage < 0 || $percentage > 100) {
            throw InvalidArgumentExceptionFactory::createForInvalidOptionalFlagBoundaries($percentage);
        }

        $this->percentage = $percentage;
    }

    /** Element of ]0;100[.
     */
    public function getPercentage(): int
    {
        return $this->percentage;
    }
    
    public function __toString(): string
    {
        return '%?';
    }
}
