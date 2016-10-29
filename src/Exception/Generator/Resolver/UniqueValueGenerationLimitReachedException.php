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

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

class UniqueValueGenerationLimitReachedException extends \RuntimeException implements ResolutionThrowable
{
    /**
     * @return static
     */
    public static function create(UniqueValue $value, int $limit, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not generate a unique value after %d attempts for "%s".',
                $limit,
                $value->getId()
            ),
            $code,
            $previous
        );
    }
}
