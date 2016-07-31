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

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\NotClonableTrait;

abstract class AbstractChainableParserAwareParser implements ChainableTokenParserInterface, ParserAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ParserInterface|null
     */
    protected $parser;

    public function __construct(ParserInterface $parser = null)
    {
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser): self
    {
        return new static($parser);
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        if (null === $this->parser) {
            throw ParserNotFoundException::createUnexpectedCall(__METHOD__);
        }
    }
}
