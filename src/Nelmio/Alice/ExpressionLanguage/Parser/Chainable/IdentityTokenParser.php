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

use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class IdentityTokenParser extends AbstractChainableParserAwareParser
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::IDENTITY_TYPE;
    }

    /**
     * Parses '<{paramKey}>', '<{nested_<{param}>}>'.
     *
     * {@inheritdoc}
     */
    public function parse(Token $token)
    {
        parent::parse($token);

        $realValue = preg_replace('/<\((.*)\)>/', '<identity($1)>', $token->getValue());
        if (null === $realValue) {
            throw new ParseException(
                sprintf(
                    'Could not parse the value "%s".',
                    $token->getValue()
                )
            );
        }

        return $this->parser->parse($realValue);
    }
}
