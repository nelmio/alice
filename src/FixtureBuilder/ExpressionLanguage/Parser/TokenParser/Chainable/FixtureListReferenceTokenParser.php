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

use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\NullListNameDenormalizer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;

/**
 * @internal
 */
final class FixtureListReferenceTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = NullListNameDenormalizer::REGEX;

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__, true);
    }
    
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::LIST_REFERENCE_TYPE;
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
        $references = $this->buildReferences($token);

        return new ArrayValue($references);
    }

    /**
     * @throws ParseException
     *
     * @return list<FixtureReferenceValue>
     *
     * @example
     *  "@user_{alice, bob}" => ['user_alice', 'user_bob']
     */
    private function buildReferences(Token $token): array
    {
        $matches = [];
        $name = (string) substr($token->getValue(), 1);

        if (1 !== preg_match(self::REGEX, $name, $matches)) {
            throw ExpressionLanguageExceptionFactory::createForUnparsableToken($token);
        }

        $listElements = preg_split('/\s*,\s*/', $matches['list']);

        $references = [];
        foreach ($listElements as $element) {
            $fixtureId = str_replace(
                sprintf('{%s}', $matches['list']),
                $element,
                $name
            );
            $references[] = new FixtureReferenceValue($fixtureId);
        }

        return $references;
    }
}
