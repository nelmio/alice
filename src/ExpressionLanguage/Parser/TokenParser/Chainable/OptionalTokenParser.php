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

use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class OptionalTokenParser extends AbstractChainableParserAwareParser
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::OPTIONAL_TYPE;
    }

    /**
     * Parses "10x @user*", "<randomNumber(0, 10)x @user<{param}>*", etc.
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): OptionalValue
    {
        parent::parse($token);

        if (1 !== preg_match(
                '/^(?<quantifier>\d+|\d*\.\d+|<.+>)%\? (?<first_member>[^:]+)(?:\: (?<second_member>[^\ ]+))?/',
                $token->getValue(),
                $matches
            )
        ) {
            throw new ParseException(
                sprintf(
                    'Could not parse the value "%s".',
                    $token->getValue()
                )
            );
        }

        return new OptionalValue(
            $this->parser->parse($matches['quantifier']),
            $this->parser->parse($matches['first_member']),
            array_key_exists('second_member', $matches) ? $this->parser->parse($matches['second_member']) : null
        );
    }
}
