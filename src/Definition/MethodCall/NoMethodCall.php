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
use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

/**
 * Represents an absence of method call. Is used for example when a fixtures has 'constructor: false'. The difference
 * with 'null' would be that null represents the absence of a method specified, which in the context of the constructor
 * is very different from specifying "no call".
 */
final class NoMethodCall implements MethodCallInterface
{
    public function withArguments(array $arguments = null): never
    {
        $this->throwException(__METHOD__);
    }
    
    public function getCaller(): never
    {
        $this->throwException(__METHOD__);
    }
    
    public function getMethod(): never
    {
        $this->throwException(__METHOD__);
    }
    
    public function getArguments(): never
    {
        $this->throwException(__METHOD__);
    }
    
    public function __toString(): string
    {
        return 'none';
    }

    private function throwException(string $method): never
    {
        throw LogicExceptionFactory::createForUncallableMethod($method);
    }
}
