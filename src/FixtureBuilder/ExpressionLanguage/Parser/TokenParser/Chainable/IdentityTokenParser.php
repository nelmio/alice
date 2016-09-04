<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;

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
     * Parses expressions such as '<(something)>'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        parent::parse($token);

        $realValue = preg_replace('/<\((.*)\)>/', '<identity($1)>', $token->getValue());
        if (null === $realValue) {
            throw ParseException::createForToken($token);
        }

        return $this->parser->parse($realValue);
    }
}
