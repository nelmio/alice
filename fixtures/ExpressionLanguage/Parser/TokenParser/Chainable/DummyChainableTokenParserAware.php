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

use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;

class DummyChainableTokenParserAware implements ChainableTokenParserInterface, ParserAwareInterface
{
    /**
     * @var ParserInterface|null
     */
    public $parser;

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        return '';
    }
}
