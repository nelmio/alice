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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * @private
 */
final class UnresolvableValueExceptionFactory
{
    public static function create(ValueInterface $value, int $code = 0, \Throwable $previous = null): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Could not resolve value "%s".',
                $value
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidReferenceId(ValueInterface $value, $result, int $code = 0, \Throwable $previous = null): UnresolvableValueException
    {
        return new UnresolvableValueException(
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

    public static function createForCouldNotEvaluateExpression(ValueInterface $value, int $code = 0, \Throwable $previous = null): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Could not evaluate the expression "%s".',
                $value->getValue()
            ),
            $code,
            $previous
        );
    }

    public static function createForCouldNotFindVariable(ValueInterface $value, int $code = 0, \Throwable $previous = null): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Could not find a variable "%s".',
                $value->getValue()
            ),
            $code,
            $previous
        );
    }

    public static function createForCouldNotFindParameter(string $parameterKey): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Could not find the parameter "%s".',
                $parameterKey
            )
        );
    }

    /**
     * @param ValueInterface               $quantifier
     * @param null|object|array|float|bool $resolvedQuantifier
     *
     * @return UnresolvableValueException
     */
    public static function createForInvalidResolvedQuantifierTypeForOptionalValue(ValueInterface $quantifier, $resolvedQuantifier): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Expected the quantifier "%s" for the optional value to be resolved into a string, got "%s" instead.',
                get_class($quantifier),
                is_object($resolvedQuantifier) ? get_class($resolvedQuantifier) : gettype($resolvedQuantifier)
            )
        );
    }

    public static function createForNoFixtureOrObjectMatchingThePattern(ValueInterface $value): UnresolvableValueException
    {
        return new UnresolvableValueException(
            sprintf(
                'Could not find a fixture or object ID matching the pattern "%s".',
                $value->getValue()
            )
        );
    }

    private function __construct()
    {
    }
}
