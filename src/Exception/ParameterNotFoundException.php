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

namespace Nelmio\Alice\Exception;

class ParameterNotFoundException extends \UnexpectedValueException
{
    /**
     * @return static
     */
    public static function create(string $key, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not find the parameter "%s".',
                $key
            ),
            $code,
            $previous
        );
    }
}
