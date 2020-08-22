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

use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @internal
 */
interface LexerInterface
{
    /**
     * Converts a string into a sequence of tokens.
     *
     *
     * @throws ExpressionLanguageParseThrowable
     *
     * @return Token[]
     */
    public function lex(string $value): array;
}
