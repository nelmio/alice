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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Throwable;

/**
 * @private
 */
final class ExpressionLanguageExceptionFactory
{
    public static function createForNoParserFoundForToken(Token $token): ParserNotFoundException
    {
        return new ParserNotFoundException(
            sprintf(
                'No suitable token parser found to handle the token "%s" (type: %s).',
                $token->getValue(),
                $token->getType()
            )
        );
    }

    public static function createForExpectedMethodCallOnlyIfHasAParser(string $method): ParserNotFoundException
    {
        return new ParserNotFoundException(
            sprintf(
                'Expected method "%s" to be called only if it has a parser.',
                $method
            )
        );
    }

    public static function createForUnparsableToken(Token $token, int $code = 0, Throwable $previous = null): ParseException
    {
        return new ParseException(
            sprintf(
                'Could not parse the token "%s" (type: %s).',
                $token->getValue(),
                $token->getType()
            ),
            $code,
            $previous
        );
    }

    public static function createForMalformedFunction(string $value): MalformedFunctionException
    {
        return new MalformedFunctionException(
            sprintf(
                'The value "%s" contains an unclosed function.',
                $value
            )
        );
    }

    public static function createForCouldNotLexValue(string $value): LexException
    {
        return new LexException(
            sprintf(
                'Could not lex the value "%s".',
                $value
            )
        );
    }

    private function __construct()
    {
    }
}
