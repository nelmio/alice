<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Lexer;

use Nelmio\Alice\Exception\ExpressionLanguage\LexException;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class GlobalPatternsLexer implements LexerInterface
{
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
