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

/**
 * @private
 */
final class FunctionTreeTokenizer
{
    use IsAServiceTrait;

    /** @private */
    const DELIMITER= '___##';

    /**
     * Replaces the function delimiters by tokens to easily identify them in the future and return the value splat into
     * tokens.
     *
     * Example:
     *  'foo <f(<g()>)> bar'
     *  will result in:
     *  [
     *      'foo ',
     *      'FUNCTION_START__f__',
     *      'FUNCTION_START__g__',
     *      'IDENTITY_OR_FUNCTION_END',
     *      'IDENTITY_OR_FUNCTION_END',
     *      ' bar',
     *  ]
     */
    public function tokenize(string $value): array
    {
        $value = preg_replace(
            '/(.*?)<((?:.*?:)?(?:\p{L}|_|[0-9])+?)\((.*?)/',
            sprintf('$1%1$sFUNCTION_START__$2__%1$s$3', self::DELIMITER),
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

        return preg_split(sprintf('/%s/', self::DELIMITER), $value, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Replaces the tokens by their original values.
     */
    public function detokenize(string $value): string
    {
        $count = 1;
        while ($count !== 0) {
            $value = preg_replace(
                '/FUNCTION_START__(.*?)__/',
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

    public function isOpeningToken(string $value): bool
    {
        return 'FUNCTION_START__' === substr($value, 0, 16) || 'IDENTITY_START' === substr($value, 0, 14);
    }

    public function isClosingToken(string $value): bool
    {
        return 'IDENTITY_OR_FUNCTION_END' === $value;
    }

    public function functionIsNotClosed(array $functions): bool
    {
        return [] !== $functions;
    }

    public function isTheLastFunction(array $functions): bool
    {
        return 1 === count($functions);
    }
}
