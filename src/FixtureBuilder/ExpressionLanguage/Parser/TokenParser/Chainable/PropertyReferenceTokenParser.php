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

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class PropertyReferenceTokenParser extends AbstractChainableParserAwareParser
{
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::PROPERTY_REFERENCE_TYPE;
    }

    /**
     * Parses tokens values like "@user->username".
     *
     *
     *
     * @throws ParseException
     */
    public function parse(Token $token): FixturePropertyValue
    {
        parent::parse($token);

        $explodedValue = explode('->', $token->getValue());
        if (count($explodedValue) !== 2) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $reference = $this->parser->parse($explodedValue[0]);
        if (false === $reference instanceof ValueInterface) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        return new FixturePropertyValue(
            $reference,
            $explodedValue[1]
        );
    }
}
