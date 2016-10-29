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

use Nelmio\Alice\Definition\ValueInterface;

class ResolverNotFoundException extends \LogicException
{
    /**
     * @return static
     */
    public static function createForParameter(string $parameterKey, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'No resolver found to resolve parameter "%s".',
                $parameterKey
            ),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createForValue(ValueInterface $value, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'No resolver found to resolve value "%s".',
                $value
            ),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createUnexpectedCall(string $method, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a resolver.',
                $method
            ),
            $code,
            $previous
        );
    }
}
