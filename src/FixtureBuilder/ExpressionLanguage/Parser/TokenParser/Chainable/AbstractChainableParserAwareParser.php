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

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory;
use Nelmio\Alice\Throwable\ParseThrowable;

/**
 * @internal
 */
abstract class AbstractChainableParserAwareParser implements ChainableTokenParserInterface, ParserAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ParserInterface|null
     */
    protected $parser;

    public function __construct(ParserInterface $parser = null)
    {
        $this->parser = $parser;
    }
    
    public function withParser(ParserInterface $parser)
    {
        return new static($parser);
    }

    /**
     * @throws ParseThrowable
     *
     * @return ValueInterface|string|array
     */
    public function parse(Token $token)
    {
        if (null === $this->parser) {
            throw ExpressionLanguageExceptionFactory::createForExpectedMethodCallOnlyIfHasAParser(__METHOD__);
        }
    }
}
