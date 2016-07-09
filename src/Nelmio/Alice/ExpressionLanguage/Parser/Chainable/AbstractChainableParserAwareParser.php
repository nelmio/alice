<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\Chainable;

use Nelmio\Alice\ExpressionLanguage\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;

abstract class AbstractChainableParserAwareParser implements ChainableTokenParserInterface, ParserAwareInterface
{
    /**
     * @var ParserInterface|null
     */
    protected $parser;

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser): self
    {
        $clone = clone $this;
        $clone->parser = $parser;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        if (null === $this->parser) {
            throw new \BadMethodCallException(
                sprintf(
                    'Expected method "%s" to be called only if it has a parser.',
                    __METHOD__
                )
            );
        }
    }

    public function __clone()
    {
        if (null !== $this->parser) {
            $this->parser = clone $this->parser;
        }
    }
}
{

}
