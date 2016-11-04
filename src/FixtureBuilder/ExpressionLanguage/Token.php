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

/**
 * @internal
 */
final class Token
{
    /**
     * @var string
     */
    private $value;
    
    /**
     * @var TokenType
     */
    private $type;

    public function __construct(string $value, TokenType $type)
    {
        $this->value = $value;
        $this->type = $type;
    }
    
    public function withValue(string $value): self
    {
        $clone = clone $this;
        $clone->value = $value;
        
        return $clone;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type->getValue();
    }

    public function __toString()
    {
        return sprintf('(%s) %s', $this->type->getValue(), $this->value);
    }
}
