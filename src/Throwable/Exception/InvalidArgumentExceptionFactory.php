<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Throwable\Exception;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;

/**
 * @private
 */
final class InvalidArgumentExceptionFactory
{
    public static function createForInvalidReferenceType(string $reference): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected reference to be either a string or a "%s" instance, got "%s" instead.',
                ValueInterface::class,
                $reference
            )
        );
    }

    public static function createForReferenceKeyMismatch(string $id1, string $id2): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Reference key mismatch, the keys "%s" and "%s" refers to the same fixture but the keys are different.',
                $id1,
                $id2
            )
        );
    }

    public static function createForFlagBagKeyMismatch(FixtureInterface $fixture, FlagBag $flags): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected the fixture ID and the flags key to be the same. Got "%s" and "%s" instead.',
                $fixture->getId(),
                $flags->getKey()
            )
        );
    }

    /**
     * @param int|float|string $seed
     *
     * @return \InvalidArgumentException
     */
    public static function createForInvalidSeedConfigurationValue($seed): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected value "nelmio_alice.seed" to be either null or a strictly positive integer but got "%s" '
                .'instead.',
                $seed
            )
        );
    }

    public static function createForRedundantUniqueValue(string $id): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Cannot create a unique value of a unique value for value "%s".',
                $id
            )
        );
    }

    public static function createForInvalidExpressionLanguageTokenType(string $type): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected type to be a known token type but got "%s".',
                $type
            )
        );
    }

    public static function createForInvalidExpressionLanguageToken(string $value): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Invalid token "%s" found.',
                $value
            )
        );
    }

    public static function createForNoIncludeStatementInData(string $file): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Could not find any include statement in the file "%s".',
                $file
            )
        );
    }

    public static function createForEmptyIncludedFileInData(string $file): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected elements of include statement to be file names. Got empty string instead in file '
                .'"%s".',
                $file
            )
        );
    }

    public static function createForFileCouldNotBeFound(string $file, int $code = 0, \Throwable $previous = null): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'The file "%s" could not be found.',
                $file
            ),
            $code,
            $previous
        );
    }

    public static function createForInvalidLimitValue(int $limit): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected limit value to be a strictly positive integer, got "%d" instead.',
                $limit
            )
        );
    }

    public static function createForInvalidLimitValueForRecursiveCalls(int $limit): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Expected limit for recursive calls to be of at least 2. Got "%d" instead.',
                $limit
            )
        );
    }

    public static function createForInvalidFakerFormatter(string $formatter): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Invalid faker formatter "%s" found.',
                $formatter
            )
        );
    }

    public static function createForFixtureExtendingANonTemplateFixture(FixtureInterface $fixture, string $fixtureId): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Fixture "%s" extends "%2$s" but "%2$s" is not a template.',
                $fixture->getId(),
                $fixtureId
            )
        );
    }

    public static function createForUnsupportedTypeForIdenticalValuesCheck($value): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Unsupported type "%s": cannot determine if two values of this type are identical.',
                gettype($value)
            )
        );
    }

    public static function createForInvalidConstructorMethod(string $method): \InvalidArgumentException
    {
        return new \InvalidArgumentException(
            sprintf(
                'Invalid constructor method "%s".',
                $method
            )
        );
    }
}
