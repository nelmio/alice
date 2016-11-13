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

use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

class UnresolvableValueException extends \RuntimeException implements ResolutionThrowable
{
    public static function create(ValueInterface $value, int $code = 0, \Throwable $previous = null): self
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

    public static function createForInvalidReferenceId(ValueInterface $value, $result, int $code = 0, \Throwable $previous = null): self
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

    public static function createForCouldNotEvaluateExpression(ValueInterface $value, int $code = 0, \Throwable $previous = null): self
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

    public static function createForCouldNotFindVariable(ValueInterface $value, int $code = 0, \Throwable $previous = null): self
    {
        return new static(
            sprintf(
                'Could not find a variable "%s".',
                $value->getValue()
            ),
            $code,
            $previous
        );
    }

    public static function createForCouldNotFindParameter(string $parameterKey): self
    {
        return new static(
            sprintf(
                'Could not find the parameter "%s".',
                $parameterKey
            )
        );
    }

    public static function createForInvalidResolvedQuantifierTypeForOptionalValue(OptionalValue $value, $quantifier): self
    {
        return new static(
            sprintf(
                'Expected the quantifier "%s" for the optional value to be resolved into a string, got "%s" instead.',
                get_class($value->getQuantifier()),
                is_object($quantifier) ? get_class($quantifier) : gettype($quantifier)
            )
        );
    }

    public static function createForNoFixtureOrObjectMatchingThePattern(ValueInterface $value): self
    {
        return new static(
            sprintf(
                'Could not find a fixture or object ID matching the pattern "%s".',
                $value->getValue()
            )
        );
    }
}
