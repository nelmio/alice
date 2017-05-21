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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser;

/**
 * @private
 */
final class FlagParserExceptionFactory
{
    public static function createForNoParserFoundForElement(string $element): FlagParserNotFoundException
    {
        return new FlagParserNotFoundException(
            sprintf(
                'No suitable flag parser found to handle the element "%s".',
                $element
            )
        );
    }

    public static function createForExpectedMethodToBeCalledIfHasAParser(string $method): FlagParserNotFoundException
    {
        return new FlagParserNotFoundException(
            sprintf(
                'Expected method "%s" to be called only if it has a flag parser.',
                $method
            )
        );
    }

    private function __construct()
    {
    }
}
