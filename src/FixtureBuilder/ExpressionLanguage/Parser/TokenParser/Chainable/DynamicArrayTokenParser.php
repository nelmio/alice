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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class DynamicArrayTokenParser extends AbstractChainableParserAwareParser
{
    /** @private */
    const REGEX = '/^(?<quantifier>\d+|<.*>)x (?<elements>.*)/';
    
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::DYNAMIC_ARRAY_TYPE;
    }

    /**
     * Parses "10x @user*", "<randomNumber(0, 10)x @user<{param}>*", etc.
     *
     *
     *
     * @throws ParseException
     */
    public function parse(Token $token): DynamicArrayValue
    {
        parent::parse($token);

        if (1 !== preg_match(self::REGEX, $token->getValue(), $matches)) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $quantifier = $this->parser->parse($matches['quantifier']);
        if (is_scalar($quantifier)) {
            $quantifier = (int) $quantifier;
        }

        return new DynamicArrayValue(
            $quantifier,
            $this->parser->parse($matches['elements'])
        );
    }
}
