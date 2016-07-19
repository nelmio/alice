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

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\Throwable\ParseThrowable;

/**
 * More specific version of {@see ParserInterface}.
 */
interface TokenParserInterface
{
    /**
     * @param Token $token
     *
     * @throws ParseThrowable
     *
     * @return ValueInterface|string
     */
    public function parse(Token $token);
}
