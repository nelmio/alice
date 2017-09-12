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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\IsAServiceTrait;

/**
 * @internal
 */
final class StringTokenParser implements ChainableTokenParserInterface
{
    use IsAServiceTrait;

    /**
     * @var ArgumentEscaper
     */
    private $argumentEscaper;

    public function __construct(ArgumentEscaper $argumentEscaper)
    {
        $this->argumentEscaper = $argumentEscaper;
    }

    /**
     * @inheritdoc
     */
    public function canParse(Token $token): bool
    {
        return $token->getType() === TokenType::STRING_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token)
    {
        return $this->argumentEscaper->unescape($token->getValue());
    }
}
