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

class ProphecyChainableTokenParserAware implements ChainableTokenParserInterface, ParserAwareInterface
{
    /**
     * @var ChainableTokenParserInterface
     */
    private $decoratedParser;

    /**
     * @var ParserAwareInterface
     */
    private $decoratedAware;

    public function __construct(ChainableTokenParserInterface $decoratedParser, ParserAwareInterface $decoratedAware)
    {
        $this->decoratedParser = $decoratedParser;
        $this->decoratedAware = $decoratedAware;
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $this->decoratedParser->canParse($token);
    }

    /**
     * @inheritdoc
     */
    public function withParser(ParserInterface $parser)
    {
        return $this->decoratedAware->withParser($parser);
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        return $this->decoratedParser->parse($token);
    }
}
