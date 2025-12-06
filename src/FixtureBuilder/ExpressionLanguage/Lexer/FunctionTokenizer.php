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

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\MalformedFunctionException;
use function array_key_last;

/**
 * @private
 */
final class FunctionTokenizer
{
    use IsAServiceTrait;

    /** @internal */
    public const DELIMITER = '___##';

    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new FunctionTreeTokenizer();
    }

    /**
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
                : $node;
        }

        return $tokenizedValue;
    }

    public function isTokenized(string $value): bool
    {
        return false !== mb_strpos($value, '<aliceTokenizedFunction(');
    }

    public function detokenize(string $value): string
    {
        if (false === $this->isTokenized($value)) {
            return $value;
        }

        $value = mb_substr($value, 24, mb_strlen($value) - 24 - 2);

        return $this->tokenizer->detokenize($value);
    }

    /**
     * Regroup tokens together by detecting when the function starts, closes or when it is nested.
     */
    private function buildTree(string $originalValue, array $tokens): array
    {
        $tree = [];
        $functions = [];

        foreach ($tokens as $key => $value) {
            if ($this->tokenizer->isOpeningToken($value)) {
                $functions[$key] = null;    // The value here is ignored

                continue;
            }

            if ($this->tokenizer->isClosingToken($value)) {
                if (false === $this->tokenizer->isTheLastFunction($functions)) {
                    $lastFunctionKey = array_key_last($functions);
                    if (null === $lastFunctionKey) {
                        throw ExpressionLanguageExceptionFactory::createForMalformedFunction($originalValue);
                    }

                    unset($functions[$lastFunctionKey]);

                    continue;
                }
                $lastFunctionKey = array_key_last($functions);
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
            throw ExpressionLanguageExceptionFactory::createForMalformedFunction($originalValue);
        }

        return $tree;
    }

    private function append(array &$tree, array $values, int $startKey, int $endKey): void
    {
        $value = '';
        for ($i = $startKey; $i < $endKey; ++$i) {
            $value .= $values[$i];
        }

        $tree[] = $value.'IDENTITY_OR_FUNCTION_END';
    }
}
