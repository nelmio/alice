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

namespace Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class SimpleReferenceTokenParser implements ChainableTokenParserInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::SIMPLE_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as "@user".
     *
     * {@inheritdoc}
     */
    public function parse(Token $token): FixtureReferenceValue
    {
        $value = $token->getValue();

        try {
            return new FixtureReferenceValue(substr($value, 1));
        } catch (\TypeError $error) {
            throw ParseException::createForToken($token, $error);
        }
    }
}
