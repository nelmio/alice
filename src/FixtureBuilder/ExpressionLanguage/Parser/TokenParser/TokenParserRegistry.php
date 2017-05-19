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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;

/**
 * @internal
 */
final class TokenParserRegistry implements TokenParserInterface, ParserAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableTokenParserInterface[]
     */
    private $parsers = [];

    /**
     * @param ChainableTokenParserInterface[] $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = (
            function (ChainableTokenParserInterface ...$parsers) {
                return $parsers;
            }
        )(...$parsers);
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser): self
    {
        $parsers = [];
        foreach ($this->parsers as $tokenParser) {
            $parsers[] = ($tokenParser instanceof ParserAwareInterface)
                ? $tokenParser->withParser($parser)
                : $tokenParser
            ;
        }

        return new self($parsers);
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($token)) {
                return $parser->parse($token);
            }
        }

        throw ExpressionLanguageExceptionFactory::createForNoParserFoundForToken($token);
    }
}
