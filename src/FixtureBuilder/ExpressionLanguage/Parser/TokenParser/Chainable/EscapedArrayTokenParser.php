<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class EscapedArrayTokenParser implements ChainableTokenParserInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::ESCAPED_ARRAY_TYPE;
    }

    /**
     * Parses '[[X]]'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token): string
    {
        $value = $token->getValue();

        try {
            return substr($value, 1, strlen($value) - 2);
        } catch (\TypeError $error) {
            throw ParseException::createForToken($token, 0, $error);
        }
    }
}
