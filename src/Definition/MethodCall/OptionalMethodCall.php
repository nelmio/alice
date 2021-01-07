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

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\MethodCallInterface;

/**
 * Represents a method call that should be called or not based on the given probability.
 */
final class OptionalMethodCall implements MethodCallInterface
{
    /**
     * @var MethodCallInterface
     */
    private $methodCall;

    /**
     * @var OptionalFlag
     */
    private $flag;

    public function __construct(MethodCallInterface $methodCall, OptionalFlag $flag)
    {
        $this->methodCall = $methodCall;
        $this->flag = $flag;
    }
    
    public function withArguments(array $arguments = null): self
    {
        $clone = clone $this;
        $clone->methodCall = $clone->methodCall->withArguments($arguments);

        return $clone;
    }
    
    public function getCaller()
    {
        return $this->methodCall->getCaller();
    }
    
    public function getMethod(): string
    {
        return $this->methodCall->getMethod();
    }
    
    public function getArguments()
    {
        return $this->methodCall->getArguments();
    }

    /**
     * @return int Element of ]0;100[.
     */
    public function getPercentage(): int
    {
        return $this->flag->getPercentage();
    }

    public function getOriginalMethodCall(): MethodCallInterface
    {
        return $this->methodCall;
    }
    
    public function __toString(): string
    {
        return $this->methodCall->__toString();
    }
}
