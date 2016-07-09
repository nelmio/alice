<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\Exception\ExpressionLanguage\ParserNotFoundException;
use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenParserInterface;
use Nelmio\Alice\FixtureBuilder\Parser\ChainableParserInterface;

final class TokenParserRegistry implements TokenParserInterface, ParserAwareInterface
{
    /**
     * @var ChainableParserInterface[]
     */
    private $parsers = [];
    
    /**
     * @param ChainableParserInterface[] $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = (
        function (ChainableParserInterface ...$parsers) {
            return $parsers;
        }
        )(...$parsers);
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser): self
    {
        $clone = clone $this;
        foreach ($clone->parsers as $index => $tokenParser) {
            if ($tokenParser instanceof ParserAwareInterface) {
                $clone->parsers[$index] = $tokenParser->withParser($parser);
            }
        }
        
        return $clone;
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

        throw new ParserNotFoundException(
            sprintf(
                'No suitable token parser found to handle the token "%s".',
                $token
            )
        );
    }
    
    public function __clone()
    {
        foreach ($this->parsers as $index => $parser) {
            $this->parsers[$index] = clone $parser;
        }
    }
}
