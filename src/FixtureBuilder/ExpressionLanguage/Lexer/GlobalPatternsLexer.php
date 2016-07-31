<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class GlobalPatternsLexer implements LexerInterface
{
    use NotClonableTrait;

    const PATTERNS = [
        '/^((?:\d+|<.*>)x .*)/' => TokenType::DYNAMIC_ARRAY_TYPE,
        '/^.*(?:\d+|<.*>)x .*/' => null,
        '/^([^<>\[\%\$@]+)$/' => TokenType::STRING_TYPE,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        foreach (self::PATTERNS as $pattern => $tokenTypeConstant) {
            if (1 === preg_match($pattern, $value, $matches)) {
                if (null === $tokenTypeConstant) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Invalid token "%s" found.',
                            $value
                        )
                    );
                }

                return [new Token($matches[1], new TokenType($tokenTypeConstant))];
            }
        }

        throw LexException::create($value);
    }
}
