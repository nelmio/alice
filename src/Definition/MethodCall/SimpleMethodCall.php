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

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ValueInterface;

/**
 * Minimalist implementation.
 */
final class SimpleMethodCall implements MethodCallInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var ValueInterface[]|array|null
     */
    private $arguments;

    /**
     * @param ValueInterface[]|array|null $arguments
     */
    public function __construct(string $method, array $arguments = null)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }
    
    public function withArguments(array $arguments = null): self
    {
        $clone = clone $this;
        $clone->arguments = $arguments;

        return $clone;
    }
    
    public function getCaller()
    {
        return null;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getArguments()
    {
        return $this->arguments;
    }
    
    public function __toString(): string
    {
        return $this->method;
    }
}
