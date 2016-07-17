<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @var array
     */
    private $arguments;

    /**
     * @param string                   $method
     * @param ValueInterface[]|mixed[] $arguments
     */
    public function __construct(string $method, array $arguments)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function withArguments(array $arguments = null): self
    {
        return new self($this->method, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array 
    {
        return $this->arguments;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->method;
    }
}
