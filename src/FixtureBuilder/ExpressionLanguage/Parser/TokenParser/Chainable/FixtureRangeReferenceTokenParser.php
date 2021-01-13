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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\RangeName;
use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullRangeNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class FixtureRangeReferenceTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = NullRangeNameDenormalizer::REGEX;

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__);
    }

    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::RANGE_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as '$username'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        $range = $this->buildRange($token);
        $references = [];
        $from = $range->getFrom();
        $to = $range->getTo();
        $step = $range->getStep();
        for ($currentIndex = $from; $currentIndex <= $to; $currentIndex += $step) {
            $fixtureId = str_replace($this->token, (string) $currentIndex, $range->getName());
            $references[] = new FixtureReferenceValue($fixtureId);
        }

        return new ArrayValue($references);
    }

    /**
     * @throws ParseException
     *
     * @example
     *  "@user{1..10}" => new RangeName('user', 1, 10)
     */
    private function buildRange(Token $token): RangeName
    {
        $matches = [];
        $name = substr($token->getValue(), 1);

        if (1 !== preg_match(self::REGEX, (string) $name, $matches)) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $reference = str_replace(sprintf('{%s}', $matches['range']), $this->token, $name);

        $step = 1;

        if (isset($matches['step'])) {
            $step = ((int) $matches['step']) ?: 1;
        }

        return new RangeName($reference, (int) $matches['from'], (int) $matches['to'], $step);
    }
}
