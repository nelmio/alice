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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\Throwable\ParseThrowable;

/**
 * More specific version of {@see \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface}.
 *
 * @internal
 */
interface TokenParserInterface
{
    /**
     * @param Token $token
     *
     * @throws ParseThrowable
     *
     * @return ValueInterface|string|array
     */
    public function parse(Token $token);
}
