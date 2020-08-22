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
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * @internal
 */
final class GlobalPatternsLexer implements LexerInterface
{
    use IsAServiceTrait;

    const PATTERNS = [
        '/^(?:\d+|<.*>)x .*/' => TokenType::DYNAMIC_ARRAY_TYPE,
        '/^.*(?:\d+|<.*>)x .*/' => null,
        '/^[^<>\[\%\$@\\\]+$/' => TokenType::STRING_TYPE,
    ];

    /**
     * @var LexerInterface
     */
    private $lexer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->lexer = $decoratedLexer;
    }

    /**
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

        return $this->lexer->lex($value);
    }
}
