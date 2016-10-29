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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;

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
