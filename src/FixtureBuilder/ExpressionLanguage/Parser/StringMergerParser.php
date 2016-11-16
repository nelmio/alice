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

use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @internal
 */
final class StringMergerParser implements ParserInterface
{
    use IsAServiceTrait;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Uses the decorated parser to parse the value and then walk through the list of values to merge two successive
     * strings.
     *
     * {@inheritdoc}
     */
    public function parse(string $value)
    {
        $parsedValue = $this->parser->parse($value);
        if (false === $parsedValue instanceof ListValue) {
            return $parsedValue;
        }

        $mergedValues = array_reduce(
            $parsedValue->getValue(),
            [$this, 'mergeStrings'],
            $initial = []
        );

        return (1 === count($mergedValues))
            ? $mergedValues[0]
            : new ListValue($mergedValues)
        ;
    }
    
    private function mergeStrings(array $values, $value): array
    {
        if (false === is_string($value)) {
            $values[] = $value;

            return $values;
        }

        $lastElement = end($values);
        if (false === is_string($lastElement)) {
            $values[] = $value;

            return $values;
        }

        $values[key($values)] = $lastElement.$value;

        return $values;
    }
}
