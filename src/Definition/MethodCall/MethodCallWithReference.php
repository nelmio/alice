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
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\Definition\ValueInterface;

/**
 * Represents a method call for which the caller has been specified.
 */
final class MethodCallWithReference implements MethodCallInterface
{
    /**
     * @var ServiceReferenceInterface
     */
    private $caller;

    /**
     * @var string
     */
    private $method;

    /**
     * @var ValueInterface[]|array|null
     */
    private $arguments;

    /**
     * @var string
     */
    private $stringValue;

    /**
     * @param ValueInterface[]|array|null $arguments
     */
    public function __construct(ServiceReferenceInterface $caller, string $method, array $arguments = null)
    {
        $this->caller = clone $caller;
        $this->method = $method;
        $this->arguments = $arguments;

        if ($caller instanceof StaticReference) {
            $this->stringValue = $caller->getId().'::'.$method;
        } else {
            $this->stringValue = $caller->getId().'->'.$method;
        }
    }
    
    public function withArguments(array $arguments = null): self
    {
        $clone = clone $this;
        $clone->arguments = $arguments;

        return $clone;
    }
    
    public function getCaller()
    {
        return clone $this->caller;
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
        return $this->stringValue;
    }
}
