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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Exception\ExpressionLanguage\ParseException;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

final class MethodReferenceTokenParser extends AbstractChainableParserAwareParser
{
    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::METHOD_REFERENCE_TYPE;
    }

    /**
     * Parses tokens values like "@user->getUserName()".
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token): FixtureMethodCallValue
    {
        parent::parse($token);

        $explodedValue = explode('->', $token->getValue());
        if (count($explodedValue) !== 2) {
            throw ParseException::createForToken($token);
        }

        $reference = $this->parser->parse($explodedValue[0]);
        $method = $this->parser->parse(
            sprintf(
                '<%s>',
                $explodedValue[1]
            )
        );

        if ($reference instanceof FixtureReferenceValue && $method instanceof FunctionCallValue) {
            return new FixtureMethodCallValue($reference, $method);
        }

        throw ParseException::createForToken($token);
    }
}
