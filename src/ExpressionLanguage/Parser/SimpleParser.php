<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class SimpleParser implements ParserInterface
{
    use NotClonableTrait;

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
            $parsedTokens[] = $this->tokenParser->parse($token);
            $parsedTokens = $this->mergeStringTokens($parsedTokens);
        }

        if (1 === count($parsedTokens)) {
            return $parsedTokens[0];
        }

        return new ListValue($parsedTokens);
    }

    /**
     * If the last two tokens were parsed into strings, they are combined into one.
     *
     * @param ValueInterface[]|mixed[] $values
     *
     * @return ValueInterface[]|mixed[]
     */
    private function mergeStringTokens(array $values)
    {
        /** @var ValueInterface|mixed|false $lastValue */
        $lastValue = end($values);
        if (false === $lastValue || count($values) < 2 || false === is_string($lastValue)) {
            return $values;
        }

        $lastValueKey = key($values);
        $previousValueKey = $lastValueKey - 1;
        /** @var ValueInterface|mixed $previousValue */
        $previousValue = $values[$lastValueKey - 1];
        if (false === is_string($previousValue)) {
            return $values;
        }

        $values[$previousValueKey] = $previousValue.$lastValue;
        unset($values[$lastValueKey]);

        return array_values($values);
    }
}
