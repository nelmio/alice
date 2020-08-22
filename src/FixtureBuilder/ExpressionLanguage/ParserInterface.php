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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @internal
 */
interface ParserInterface
{
    /**
     * Parses a value, e.g. 'foo' or '$username' to determine if is a regular value (like 'foo') or is a value that
     * must be processed (like '$username'). If the value must be processed, it will be parsed to generate a value (a
     * ValueInterface instance) ready for processing.
     *
     *
     * @throws ExpressionLanguageParseThrowable
     *
     * @return ValueInterface|string|array
     */
    public function parse(string $value);
}
