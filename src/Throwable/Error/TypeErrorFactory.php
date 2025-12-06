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

namespace Nelmio\Alice\Throwable\Error;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use TypeError;
use function get_debug_type;

/**
 * @private
 */
final class TypeErrorFactory
{
    public static function createForDynamicArrayQuantifier($quantifier): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected quantifier to be either an integer or a "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($quantifier),
            ),
        );
    }

    public static function createForDynamicArrayElement($element): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected element to be either string, an array or a "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($element),
            ),
        );
    }

    public static function createForOptionalValueQuantifier($quantifier): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected quantifier to be either a scalar value or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($quantifier),
            ),
        );
    }

    public static function createForOptionalValueFirstMember($firstMember): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected first member to be either a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($firstMember),
            ),
        );
    }

    public static function createForOptionalValueSecondMember($secondMember): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected second member to be either null, a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($secondMember),
            ),
        );
    }

    public static function createForInvalidParameterKey($parameterKey): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected parameter key to be either a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                get_debug_type($parameterKey),
            ),
        );
    }

    public static function createForInvalidDenormalizerType(int $index, $denormalizer): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected denormalizer %d to be a "%s". Got "%s" instead.',
                $index,
                ChainableFixtureDenormalizerInterface::class,
                get_debug_type($denormalizer),
            ),
        );
    }

    public static function createForInvalidSpecificationBagMethodCall($methodCall): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected method call value to be an array. Got "%s" instead.',
                gettype($methodCall),
            ),
        );
    }

    public static function createForInvalidSpecificationBagMethodCallName($unparsedMethod): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected method name. Got "%s" instead.',
                gettype($unparsedMethod),
            ),
        );
    }

    public static function createForInvalidFixtureBagParameters($fixturesParameters): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected parameters to be an array. Got "%s" instead.',
                get_debug_type($fixturesParameters),
            ),
        );
    }

    public static function createForInvalidIncludeStatementInData($include, string $file): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected include statement to be either null or an array of files to include. Got "%s" instead '
                .'in file "%s".',
                gettype($include),
                $file,
            ),
        );
    }

    public static function createForInvalidIncludedFilesInData($includeFile, string $file): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected elements of include statement to be file names. Got "%s" instead in file "%s".',
                gettype($includeFile),
                $file,
            ),
        );
    }

    public static function createForInvalidFixtureFileReturnedData(string $file): TypeError
    {
        return new TypeError(
            sprintf(
                'The file "%s" must return a PHP array.',
                $file,
            ),
        );
    }

    public static function createForInvalidChainableParameterResolver($resolver): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected resolvers to be "%s" objects. Got "%s" instead.',
                ParameterResolverInterface::class,
                is_object($resolver) ? $resolver::class : $resolver,
            ),
        );
    }

    private function __construct()
    {
    }
}
