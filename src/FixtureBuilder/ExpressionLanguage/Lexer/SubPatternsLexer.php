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

final class SubPatternsLexer implements LexerInterface
{
    use NotClonableTrait;

    const REFERENCE_LEXER = 'reference';

    const PATTERNS = [
        '/^((?:\d+|<.+>)%\? [^:]+:[^\ ]+)/' => null,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? [^:]+(?:\: [^\ ]+)?)/' => TokenType::OPTIONAL_TYPE,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? : ?[^\ ]+?)/' => null,
        '/^(<<|>>)/' => TokenType::ESCAPED_ARROW_TYPE,
        '/^(<{[^\ <]+}>)/' => TokenType::PARAMETER_TYPE,
        '/^(<\(\S+\)>)/' => TokenType::IDENTITY_TYPE,
        '/^(<\S+\(\S*\)>)/' => TokenType::FUNCTION_TYPE,
        '/^(<\S+>)/' => null,
        '/^(\[\[.*\]\])/' => TokenType::ESCAPED_ARRAY_TYPE,
        '/^(\[[^\[\]]+\])/' => TokenType::STRING_ARRAY_TYPE,
        '/^(@@)[^\ @]*/' => TokenType::ESCAPED_REFERENCE_TYPE,
        '/^(@[^\ @]+(?:\{.*\})*)/' => self::REFERENCE_LEXER,
        '/^(\${2})/' => TokenType::ESCAPED_VARIABLE_TYPE,
        '/^(\$[^\$\ ]+)/' => TokenType::VARIABLE_TYPE,
        '/^([^<>\[\d\%\$@]+)/' => TokenType::STRING_TYPE,
        '/^([^<>\[\%\$@]+)/' => TokenType::STRING_TYPE,
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
     * {@inheritdoc}
     *
     * @throws LexException
     */
    public function lex(string $value): array
    {
        $offset = 0;
        $valueLength = strlen($value);
        $tokens = [];

        while($offset < $valueLength) {
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
     * @param LexerInterface $referenceLexer
     * @param string         $valueFragment
     *
     * @throws LexException
     *
     * @return Token[]
     */
    private function lexFragment(LexerInterface $referenceLexer, string $valueFragment): array
    {
        foreach (self::PATTERNS as $pattern => $tokenTypeConstant) {
            if (1 === preg_match($pattern, $valueFragment, $matches)) {
                if (null === $tokenTypeConstant) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Invalid token "%s" found.',
                            $valueFragment
                        )
                    );
                }

                $match = $matches[1];
                if (self::REFERENCE_LEXER === $tokenTypeConstant) {
                    return $referenceLexer->lex($match);
                }

                return [new Token($match, new TokenType($tokenTypeConstant))];
            }
        }

        throw LexException::create($valueFragment);
    }
}
