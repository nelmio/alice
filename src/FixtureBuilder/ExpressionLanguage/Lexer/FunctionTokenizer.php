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

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\MalformedFunctionException;
use Nelmio\Alice\NotClonableTrait;

/**
 * @internal
 */
final class FunctionTokenizer
{
    use NotClonableTrait;

    /** @internal */
    const DELIMITER= '___##';

    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new FunctionTreeTokenizer();
    }

    /**
     * {@inheritdoc}
     *
     * @throws MalformedFunctionException
     */
    public function tokenize(string $value): string
    {
        $tokens = $this->tokenizer->tokenize($value);
        $tree = $this->buildTree($value, $tokens);

        $tokenizedValue = '';
        foreach ($tree as $node) {
            $tokenizedValue .= ($this->tokenizer->isOpeningToken($node))
                ? sprintf('<aliceTokenizedFunction(%s)>', $node)
                : $node
            ;
        }

        return $tokenizedValue;
    }

    public function isTokenized(string $value): bool
    {
        return '<aliceTokenizedFunction(' === substr($value, 0, 24);
    }

    public function detokenize(string $value): string
    {
        if (false === $this->isTokenized($value)) {
            return $value;
        }
        $value = substr($value, 24, strlen($value) - 24 - 2);

        return $this->tokenizer->detokenize($value);
    }

    /**
     * Regroup tokens together by detecting when the function starts, closes or when it is nested.
     *
     * @param string $originalValue
     * @param array  $tokens
     *
     * @return array
     */
    private function buildTree(string $originalValue, array $tokens): array
    {
        $tree = [];
        $functions = [];

        foreach ($tokens as $key => $value) {
            if ($this->tokenizer->isOpeningToken($value)) {
                $functions[$key] = true;

                continue;
            }

            if ($this->tokenizer->isClosingToken($value)) {
                if (false === $this->tokenizer->isTheLastFunction($functions)) {
                    end($functions);
                    $lastFunctionKey = key($functions);
                    if (null === $lastFunctionKey) {
                        throw MalformedFunctionException::create($originalValue);
                    }
                    unset($functions[$lastFunctionKey]);

                    continue;
                }

                end($functions);
                $lastFunctionKey = key($functions);
                $this->append($tree, $tokens, $lastFunctionKey, $key);
                unset($functions[$lastFunctionKey]);

                continue;
            }

            if ($this->tokenizer->functionIsNotClosed($functions)) {
                continue;
            }

            $tree[] = $value;
        }

        if ([] !== $functions) {
            throw MalformedFunctionException::create($originalValue);
        }

        return $tree;
    }

    private function append(array &$tree, array $values, int $startKey, int $endKey)
    {
        $value = '';
        for ($i = $startKey; $i < $endKey; $i++) {
            $value .= $values[$i];
        }

        $tree[] = $value.'IDENTITY_OR_FUNCTION_END';
    }
}
