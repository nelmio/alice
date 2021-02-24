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

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * Value object a reference to a fixture e.g. "@user0" matching a pattern. For example "@user*" will result in a pattern
 * '~^user.*~' which can match "@user0", "@user_base" etc.
 */
final class FixtureMatchReferenceValue implements ValueInterface
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @param string $reference e.g. 'user'
     *
     * @return FixtureMatchReferenceValue reference with the pattern to match "@user*"
     */
    public static function createWildcardReference(string $reference): self
    {
        return new self(sprintf('/^%s.*/', preg_quote($reference, '/')));
    }

    public function match(string $value): bool
    {
        return 1 === preg_match($this->pattern, $value);
    }

    public function getValue(): string
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return sprintf('@(regex: %s)', $this->pattern);
    }
}
