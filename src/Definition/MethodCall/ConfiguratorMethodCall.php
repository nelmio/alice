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

/**
 * Represents a method call that is a configurator, i.e. for which the results should be kept.
 */
final class ConfiguratorMethodCall implements MethodCallInterface
{
    /**
     * @var MethodCallInterface
     */
    private $methodCall;

    public function __construct(MethodCallInterface $methodCall)
    {
        $this->methodCall = $methodCall;
    }
    
    public function withArguments(array $arguments = null): self
    {
        return new self($this->methodCall->withArguments($arguments));
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

    public function getOriginalMethodCall(): MethodCallInterface
    {
        return $this->methodCall;
    }
    
    public function __toString(): string
    {
        return $this->methodCall->__toString();
    }
}
