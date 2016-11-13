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

class FlagParserNotFoundException extends \LogicException
{
    /**
     * @return static
     */
    public static function create(string $element, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'No suitable flag parser found to handle the element "%s".',
                $element
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
                'Expected method "%s" to be called only if it has a flag parser.',
                $method
            ),
            $code,
            $previous
        );
    }
}
