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

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\UnclosedFunctionException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\NotClonableTrait;

final class FunctionLexer implements LexerInterface
{
    use NotClonableTrait;

    /** @internal */
    const DELIMITER= '___##';

    /**
     * @var LexerInterface
     */
    private $decoratedLexer;

    public function __construct(LexerInterface $decoratedLexer)
    {
        $this->decoratedLexer = $decoratedLexer;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnclosedFunctionException
     */
    public function lex(string $value): array
    {
        $tokenizedValue = $this->tokenize($value);
        $splittedValue = preg_split(sprintf('/%s/', self::DELIMITER), $tokenizedValue, -1, PREG_SPLIT_NO_EMPTY);
        $tree = $this->resolve($value, $splittedValue);

        $tokens = [];
        foreach ($tree as $item) {
            $item = $this->detokenize($item);
            $tokens = array_merge($tokens, $this->decoratedLexer->lex($item));
        }

        return $tokens;
    }

    private function tokenize(string $value): string
    {
        $value = preg_replace(
            '/(.*?)<(\S+?)\((.*?)/',
            sprintf('$1%1$sFUNCTION_START_$2_%1$s$3', self::DELIMITER),
            $value
        );
        $value = preg_replace(
            '/<\(/',
            sprintf('%1$sIDENTITY_START%1$s', self::DELIMITER),
            $value
        );
        $value = preg_replace(
            '/\)>/',
            sprintf('%1$sIDENTITY_OR_FUNCTION_END%1$s', self::DELIMITER),
            $value
        );

        return $value;
    }

    private function detokenize(string $value): string
    {
        $count = 1;
        while ($count !== 0) {
            $value = preg_replace(
                '/FUNCTION_START_(.*?)_/',
                '<$1(',
                $value,
                1,
                $count
            );
        }
        $value = preg_replace(
            '/IDENTITY_START/',
            '<(',
            $value
        );
        $value = preg_replace(
            '/IDENTITY_OR_FUNCTION_END/',
            ')>',
            $value
        );

        return $value;
    }

    public function resolve(string $originalValue, array $values)
    {
        $tree = [];
        $functions = [];

        foreach ($values as $key => $value) {
            if ($this->isOpeningToken($value)) {
                $functions[$key] = true;

                continue;
            }

            if ($this->isClosingToken($value)) {
                if (false === $this->isTheLastFunction($functions)) {
                    end($functions);
                    $lastFunctionKey = key($functions);
                    unset($functions[$lastFunctionKey]);

                    continue;
                }

                end($functions);
                $lastFunctionKey = key($functions);
                $this->append($tree, $values, $lastFunctionKey, $key);
                unset($functions[$lastFunctionKey]);

                continue;
            }

            if ($this->functionIsNotClosed($functions)) {
                continue;
            }

            $tree[] = $value;
        }

        if ([] !== $functions) {
            throw UnclosedFunctionException::create($originalValue);
        }

        return $tree;
    }

    private function isOpeningToken(string $value): bool
    {
        return 'FUNCTION_START_' === substr($value, 0, 15) || 'IDENTITY_START' === substr($value, 0, 14);
    }

    private function isClosingToken(string $value): bool
    {
        return 'IDENTITY_OR_FUNCTION_END' === $value;
    }

    private function functionIsNotClosed(array $functions): bool
    {
        return [] != $functions;
    }

    private function isTheLastFunction(array $functions): bool
    {
        return 1 === count($functions);
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
