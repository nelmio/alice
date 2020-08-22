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
final class SubPatternsLexer implements LexerInterface
{
    use IsAServiceTrait;

    const REFERENCE_LEXER = 'reference';

    const PATTERNS = [
        '/^((?:\d+|<.+>)%\? [^:]+:[^\ ]+)/' => null,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? [^:]+(?:\: +\S+)?)/' => TokenType::OPTIONAL_TYPE,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? : ?[^\ ]+?)/' => null,
        '/^(\\\<{[^\ <]+}>)/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\<\S+\(.*\)>)/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\\\[[^\[\]]+\])/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\<|\\\>)/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\[@$])/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\{2})/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^(\\\%)/' => TokenType::ESCAPED_VALUE_TYPE,
        '/^\\\$/' => null,
        '/^(<{[^\ <]+}>)/' => TokenType::PARAMETER_TYPE,
        '/^(<\(.+\)>)/' => TokenType::IDENTITY_TYPE,
        '/^(<\S+\((.|\r?\n)*\)>)/' => TokenType::FUNCTION_TYPE,
        '/^(<\S+\(.*\)>)/' => TokenType::FUNCTION_TYPE,
        '/^(<\S+>)/' => null,
        '/^(\[[^\[\]]*\])/' => TokenType::STRING_ARRAY_TYPE,
        '/^(@[^\ @\{\<]+\(.*\))/' => self::REFERENCE_LEXER, // Function with text
        '/^(@[^\ @\<]+\{.*\}->\S+\(.*\))/' => self::REFERENCE_LEXER, // Range or list with function
        '/^(@[^\ @\<]+\{.*\}->[^\(\)\ \{]+)/' => self::REFERENCE_LEXER, // Range or list with property
        '/^(@[^\ @\<]+\<[^>]+\([^)]*\)\>->[^\(\)\ \{]+)/' => self::REFERENCE_LEXER, // function with property, e.g. entity_<current()>->property
        '/^(@[^\ @\<]+\{.*\})/' => self::REFERENCE_LEXER,   // Range or list
        '/^(@[^\ @\{\<]+)/' => self::REFERENCE_LEXER,
        '/^(@)<\S+\(.*\)>/' => self::REFERENCE_LEXER,
        '/^(\$[\p{L}_\d]+)/' => TokenType::VARIABLE_TYPE,
        '/^([^\\\<>\[\d\%\$@\]]+)/' => TokenType::STRING_TYPE,
        '/^([^\\\<>\[\%\$@\]]+)/' => TokenType::STRING_TYPE,
    ];

    /**
     * @var LexerInterface
     */
    private $referenceLexer;

    public function __construct(LexerInterface $referenceLexer)
    {
        $this->referenceLexer = $referenceLexer;
    }

    /**
     * @throws LexException
     */
    public function lex(string $value): array
    {
        $offset = 0;
        $valueLength = strlen($value);
        $tokens = [];

        while ($offset < $valueLength) {
            $valueFragment = substr($value, $offset);
            $fragmentTokens = $this->lexFragment($this->referenceLexer, $valueFragment);

            foreach ($fragmentTokens as $fragmentToken) {
                $tokens[] = $fragmentToken;
                $offset += strlen($fragmentToken->getValue());
            }
        }

        return $tokens;
    }

    /**
     * @throws LexException
     *
     * @return Token[]
     */
    private function lexFragment(LexerInterface $referenceLexer, string $valueFragment): array
    {
        foreach (self::PATTERNS as $pattern => $tokenTypeConstant) {
            if (1 === preg_match($pattern, $valueFragment, $matches)) {
                if (null === $tokenTypeConstant) {
                    throw InvalidArgumentExceptionFactory::createForInvalidExpressionLanguageToken($valueFragment);
                }

                $match = $matches[1];
                if (self::REFERENCE_LEXER === $tokenTypeConstant) {
                    return $referenceLexer->lex($match);
                }

                return [new Token($match, new TokenType($tokenTypeConstant))];
            }
        }

        return [new Token($valueFragment, new TokenType(TokenType::STRING_TYPE))];
    }
}
