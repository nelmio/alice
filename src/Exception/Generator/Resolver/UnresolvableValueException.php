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
use Nelmio\Alice\Throwable\ResolutionThrowable;

class UnresolvableValueException extends \RuntimeException implements ResolutionThrowable
{
    /**
     * @return static
     */
    public static function create(ValueInterface $value, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not resolve value "%s".',
                $value
            ),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function createForInvalidReferenceId(ValueInterface $value, $result, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Expected fixture reference value "%s" to be resolved into a string. Got "%s" instead.',
                $value,
                is_object($result)
                    ? get_class($result)
                    : sprintf('(%s) %s', gettype($result), $result)
            ),
            $code,
            $previous
        );
    }

    /**
     * @return static
     */
    public static function couldNotEvaluateExpression(ValueInterface $value, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not evaluate the expression "%s".',
                $value->getValue()
            ),
            $code,
            $previous
        );
    }
}
