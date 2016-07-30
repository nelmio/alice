<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class SimpleReferenceTokenParser implements ChainableTokenParserInterface
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::SIMPLE_REFERENCE_TYPE;
    }

    /**
     * Parses "10x @user*", "<randomNumber(0, 10)x @user<{param}>*", etc.
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): FixtureReferenceValue
    {
        $value = $token->getValue();

        return new FixtureReferenceValue(substr($value, 1));
    }
}
