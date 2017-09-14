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

/**
 * @internal
 */
final class ArgumentEscaper
{
    private $tokens = [];

    public function escape(string $value): string
    {
        $token = '__ARG_TOKEN__'.hash('md5', $value);

        $this->tokens[$token] = $value;

        return $token;
    }

    public function unescape(string $value): string
    {
        return array_key_exists($value, $this->tokens) ? $this->tokens[$value] : $value;
    }
}
