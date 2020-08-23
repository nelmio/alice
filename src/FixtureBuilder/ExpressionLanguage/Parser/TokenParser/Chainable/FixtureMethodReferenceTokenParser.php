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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use TypeError;

/**
 * @internal
 */
final class FixtureMethodReferenceTokenParser extends AbstractChainableParserAwareParser
{
    use IsAServiceTrait;
    
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::METHOD_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as "@username->getName()".
     *
     *
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        parent::parse($token);

        $values = explode('->', $token->getValue());
        if (2 !== count($values)) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $fixture = $this->parser->parse($values[0]);
        $method = $this->parser->parse(sprintf('<%s>', $values[1]));

        try {
            return new FixtureMethodCallValue($fixture, $method);
        } catch (TypeError $exception) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token, 0, $exception);
        }
    }
}
