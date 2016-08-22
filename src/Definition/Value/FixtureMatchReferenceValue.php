<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * Value object a reference to a fixture e.g. "@user0" matching a pattern. For example "@user*" will result in a pattern
 * '~^user.*~' which can match "@user0", "@user_base" etc.
 *
 * @TODO: add factory for wildcard...
 */
final class FixtureMatchReferenceValue implements ValueInterface
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function match(string $value): bool
    {
        return 1 === preg_match($this->pattern, $value);
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->pattern;
    }
}
