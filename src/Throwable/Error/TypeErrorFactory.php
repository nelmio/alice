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
                is_object($quantifier) ? $quantifier::class : gettype($quantifier),
            ),
        );
    }

    public static function createForDynamicArrayElement($element): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected element to be either string, an array or a "%s". Got "%s" instead.',
                ValueInterface::class,
                is_object($element) ? $element::class : gettype($element),
            ),
        );
    }

    public static function createForOptionalValueQuantifier($quantifier): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected quantifier to be either a scalar value or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                is_object($quantifier) ? $quantifier::class : gettype($quantifier),
            ),
        );
    }

    public static function createForOptionalValueFirstMember($firstMember): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected first member to be either a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                is_object($firstMember) ? $firstMember::class : gettype($firstMember),
            ),
        );
    }

    public static function createForOptionalValueSecondMember($secondMember): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected second member to be either null, a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                is_object($secondMember) ? $secondMember::class : gettype($secondMember),
            ),
        );
    }

    public static function createForInvalidParameterKey($parameterKey): TypeError
    {
        return new TypeError(
            sprintf(
                'Expected parameter key to be either a string or an instance of "%s". Got "%s" instead.',
                ValueInterface::class,
                is_object($parameterKey) ? $parameterKey::class : gettype($parameterKey),
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
                is_object($denormalizer) ? $denormalizer::class : gettype($denormalizer),
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
                is_object($fixturesParameters) ? $fixturesParameters::class : gettype($fixturesParameters),
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
