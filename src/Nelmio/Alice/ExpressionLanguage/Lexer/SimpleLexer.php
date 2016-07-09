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

use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class SimpleLexer implements LexerInterface
{
    const PATTERNS = [
        '/^((?:\d+|<.*>)x .*)/' => TokenType::DYNAMIC_ARRAY_TYPE,
        '/^.*(?:\d+|<.*>)x .*/' => null,
    ];

    const SUB_PATTERNS = [
        '/^((?:\d+|<.+>)%\? [^:]+:[^\ ]+)/' => null,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? [^:]+(?:\: [^\ ]+)?)/' => TokenType::OPTIONAL_TYPE,
        '/^((?:\d+|\d*\.\d+|<.+>)%\? : ?[^\ ]+?)/' => null,
        '/^(<{\S+}>)/' => TokenType::PARAMETER_TYPE,
        '/^(<\(\S+\)>)/' => TokenType::IDENTITY_TYPE,
        '/^(<<\S+>>)/' => TokenType::ESCAPED_PARAMETER_TYPE,
        '/^(<\S+\)>)/' => TokenType::FUNCTION_TYPE,
        '/^(<\S+>)/' => null,
        '/^(\[\[.*\]\])/' => TokenType::ESCAPED_ARRAY,
        '/^(\[[^\[\]]+\])/' => TokenType::STRING_ARRAY,
        '/^(@@[^\ @]+)/' => TokenType::ESCAPED_REFERENCE_TYPE,

        '/^(\p{L}+[\p{L}\d\._\/]*)/' => TokenType::STRING_TYPE,

        '/^(@[^\ @]+)/' => TokenType::SIMPLE_REFERENCE_TYPE,
        '/^(\$\S+)/' => TokenType::VARIABLE_TYPE,
        '/^([^<\[\d\$@]+)/' => TokenType::STRING_TYPE,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function lex(string $value): array
    {
        foreach (self::PATTERNS as $pattern => $tokenTypeConstant) {
            if ($matchPattern = 1 === preg_match($pattern, $value, $matches)) {
                if (null === $tokenTypeConstant) {
                    throw ParseException::create($value);
                }

                return [new Token($matches[1], new TokenType($tokenTypeConstant))];
            }
        }

        $offset = 0;
        $valueLength = strlen($value);
        $tokens = [];
        while($offset < $valueLength) {
            $valueFragment = substr($value, $offset);
            foreach (self::SUB_PATTERNS as $pattern => $tokenTypeConstant) {
                if ($matchPattern = 1 === preg_match($pattern, $valueFragment, $matches)) {
                    if (null === $tokenTypeConstant) {
                        throw ParseException::create($value);
                    }

                    $match = $matches[1];
                    $tokens[] = new Token($match, new TokenType($tokenTypeConstant));
                    $offset += strlen($match);

                    break;
                }
            }

            if (true === $matchPattern) {
                continue;
            }

            $lastToken = end($tokens);
            if ($lastToken instanceof Token && $lastToken->getType() === TokenType::STRING_TYPE) {
                $tokens[key($tokens)] = new Token(
                    $lastToken->getValue().$valueFragment,
                    new TokenType(TokenType::STRING_TYPE)
                );

                break;
            }

            $tokens[] = new Token($valueFragment, new TokenType(TokenType::STRING_TYPE));

            break;
        }

        return $tokens;
    }
}
