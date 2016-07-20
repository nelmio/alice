<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\Chainable;

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class PropertyReferenceTokenParser extends AbstractChainableParserAwareParser
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::PROPERTY_REFERENCE_TYPE;
    }

    /**
     * Parses tokens values like "@user->username".
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): FixturePropertyValue
    {
        parent::parse($token);

        $explodedValue = explode('->', $token->getValue());
        if (count($explodedValue) !== 2) {
            throw ParseException::create($token->getValue());
        }

        $reference = $this->parser->parse($explodedValue[0]);

        return new FixturePropertyValue(
            $reference,
            $explodedValue[1]
        );
    }
}
