<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

class UnclosedFunctionException extends \InvalidArgumentException implements ExpressionLanguageParseThrowable
{
    /**
     * @return static
     */
    public static function create(string $value, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'The value "%s" contains an unclosed function.',
                $value
            ),
            $code,
            $previous
        );
    }
}
