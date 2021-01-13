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

use InvalidArgumentException;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;

/**
 * @internal
 */
final class SimpleReferenceTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::SIMPLE_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as "@user".
     */
    public function parse(Token $token): FixtureReferenceValue
    {
        $value = $token->getValue();

        try {
            if (!is_string($value) || '' === $value) {
                throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
            }

            return new FixtureReferenceValue(substr($value, 1));
        } catch (InvalidArgumentException $exception) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $exception);
        }
    }
}
