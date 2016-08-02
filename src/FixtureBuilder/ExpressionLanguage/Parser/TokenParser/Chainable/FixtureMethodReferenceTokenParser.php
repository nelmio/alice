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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\NotClonableTrait;

final class FixtureMethodReferenceTokenParser extends AbstractChainableParserAwareParser
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType()->getValue() === TokenType::METHOD_REFERENCE_TYPE;
    }

    /**
     * Parses expressions such as '$username'.
     *
     * {@inheritdoc}
     *
     * @throws ParseException
     */
    public function parse(Token $token)
    {
        parent::parse($token);

        $values = preg_split('/->/', $token->getValue());
        if (2 !== count($values)) {
            throw ParseException::createForToken($token);
        }

        $fixture = $this->parser->parse($values[0]);
        $method = $this->parser->parse(sprintf('<%s>', $values[1]));

        try {
            return new FixtureMethodCallValue($fixture, $method);
        } catch (\TypeError $exception) {
            throw ParseException::createForToken($token, 0, $exception);
        }
    }
}
