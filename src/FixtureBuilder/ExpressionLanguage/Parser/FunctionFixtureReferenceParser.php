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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @internal
 */
final class FunctionFixtureReferenceParser implements ParserInterface
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
     * Uses the decorated parser to parse the value and then walk through the list of values to look for fixtures
     * references followed by a function call (caused by e.g. "@user0<current()>") to correct the value and make
     * it one fixture reference instead (i.e. add the function as part of the fixture reference).
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
            [$this, 'mergeFunctionFixtureReferences'],
            []
        );

        return (1 === count($mergedValues))
            ? $mergedValues[0]
            : new ListValue($mergedValues)
        ;
    }
    
    private function mergeFunctionFixtureReferences(array $values, $value): array
    {
        $lastElement = end($values);
        if (false === $value instanceof FunctionCallValue || false === $lastElement instanceof FixtureReferenceValue) {
            $values[] = $value;

            return $values;
        }

        $values[key($values)] = new FixtureReferenceValue(
            new ListValue([
                $lastElement->getValue(),
                $value,
            ])
        );

        return $values;
    }
}
