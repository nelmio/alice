<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\ResolutionThrowable;

class RecursionLimitReachedException extends \RuntimeException implements ResolutionThrowable
{
    /**
     * @return static
     */
    public static function create(int $limit, string $key, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Recursion limit (%d tries) reached while resolving the parameter "%s"',
                $limit,
                $key
            ),
            $code,
            $previous
        );
    }
}
