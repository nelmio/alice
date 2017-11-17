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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * @internal
 */
final class ReferenceLexer implements LexerInterface
{
    use IsAServiceTrait;

    const PATTERNS = [
        '/^@.*->\S+\(.*\)/' => TokenType::METHOD_REFERENCE_TYPE,
        '/^@.*->[^\(\)\ \{]+/' => TokenType::PROPERTY_REFERENCE_TYPE,
        '/^@[^\ @\-]+\{\d+\.\.\d+\}/' => TokenType::RANGE_REFERENCE_TYPE,
        '/^@[^\ @]+\{.*,.*}/' => TokenType::LIST_REFERENCE_TYPE,
        '/^@.*\*/' => TokenType::WILDCARD_REFERENCE_TYPE,
        '/^@.*->.*/' => null,
        '/^@\S+\$\S+/' => TokenType::VARIABLE_REFERENCE_TYPE,
        '/^@\S+/' => TokenType::SIMPLE_REFERENCE_TYPE,
        '/^@/' => TokenType::SIMPLE_REFERENCE_TYPE,
    ];

    /**
     * Lex a value with the mask "@X" where X is a valid possible reference
     *
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        foreach (self::PATTERNS as $pattern => $tokenTypeConstant) {
            if (1 === preg_match($pattern, $value, $matches)) {
                if (null === $tokenTypeConstant) {
                    throw InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageToken($value);
                }

                return [new Token($matches[0], new TokenType($tokenTypeConstant))];
            }
        }

        throw ExpressionLanguageExceptionFactory::createForCouldNotLexValue($value);
    }
}
