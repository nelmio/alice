<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\ParseThrowable;

interface ParserInterface
{
    /**
     * Parses a value, e.g. 'foo' or '$username' to determine if is a regular value (like 'foo') or is a value that
     * must be processed (like '$username'). If the value must be processed, it will be parsed to generate a value (a
     * ValueInterface instance) ready for processing.
     *
     * @param string $value
     *
     * @throws ParseThrowable
     *
     * @return ValueInterface|string
     */
    public function parse(string $value);
}
