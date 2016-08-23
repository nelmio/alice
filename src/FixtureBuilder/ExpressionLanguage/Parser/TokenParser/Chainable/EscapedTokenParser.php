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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class EscapedTokenParser implements ChainableTokenParserInterface
{
    use NotClonableTrait;

    const SUPPORTED_TYPES = [
        TokenType::ESCAPED_ARROW_TYPE => true,
        TokenType::ESCAPED_REFERENCE_TYPE => true,
        TokenType::ESCAPED_VARIABLE_TYPE => true,
    ];

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return isset(self::SUPPORTED_TYPES[$token->getType()->getValue()]);
    }

    /**
     * Parses '<<', '@@'...
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): string
    {
        $value = $token->getValue();
        if ('' === $value) {
            throw ParseException::createForToken($token);
        }

        return $value[0];
    }
}
