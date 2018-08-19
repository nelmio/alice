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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\NestedValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @internal
 */
final class SimpleParser implements ParserInterface
{
    use IsAServiceTrait;

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * @var TokenParserInterface
     */
    private $tokenParser;

    public function __construct(LexerInterface $lexer, TokenParserInterface $tokenParser)
    {
        $this->lexer = $lexer;
        $this->tokenParser = ($tokenParser instanceof ParserAwareInterface)
            ? $tokenParser->withParser($this)
            : $tokenParser
        ;
    }

    /**
     * @inheritdoc
     */
    public function parse(string $value)
    {
        $tokens = $this->lexer->lex($value);
        $parsedTokens = [];
        foreach ($tokens as $token) {
            $parsedTokens = $this->parseToken($parsedTokens, $this->tokenParser, $token);
        }

        return (1 === count($parsedTokens))
            ? $parsedTokens[0]
            : new ListValue($parsedTokens)
        ;
    }

    /**
     * Parses the given token. If the value returned is a ListValue, its values will be merged to the list of parsed
     * tokens instead of adding the value itself. Another check is done to ensure that successive string tokens are
     * merged.
     *
     * @return ValueInterface[]|string[] Parsed tokens
     */
    private function parseToken(array $parsedTokens, TokenParserInterface $parser, Token $token): array
    {
        $parsedToken = $parser->parse($token);

        if ($parsedToken instanceof FunctionCallValue) {
            // Check if the pattern was something like @value_<func()> in which case the function call should be part of the reference
            // name
            $lastParsedToken = end($parsedTokens);

            if ($lastParsedToken instanceof FixtureReferenceValue) {
                $parsedTokens[key($parsedTokens)] = new FixtureReferenceValue(
                    new ListValue([
                        $lastParsedToken->getValue(),
                        $parsedToken,
                    ])
                );

                return $parsedTokens;
            }
        }

        $parsedToken = ($parsedToken instanceof NestedValue)
            ? $parsedToken->getValue()
            : [$parsedToken]
        ;

        foreach ($parsedToken as $value) {
            $parsedTokens[] = $value;
        }

        return $parsedTokens;
    }
}
