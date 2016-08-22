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

final class ReferenceLexer implements LexerInterface
{
    use NotClonableTrait;

    const PATTERNS = [
        '/^(@.*->\S+\(.*\))/' => TokenType::METHOD_REFERENCE_TYPE,
        '/^(@.*->[^\(\)\ \{]+)/' => TokenType::PROPERTY_REFERENCE_TYPE,
        '/^(@[^\ @\-]+\{\d+\.\.\d+\})/' => TokenType::RANGE_REFERENCE_TYPE,
        '/^(@[^\ @]+\{.*,.*})/' => TokenType::LIST_REFERENCE_TYPE,
        '/^(@.*\*)/' => TokenType::WILDCARD_REFERENCE_TYPE,
        '/^(@.*->.*)/' => null,
        '/^(@\S+)/' => TokenType::SIMPLE_REFERENCE_TYPE,
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
