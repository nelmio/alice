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

/**
 * Represents an absence of method call. Is used for example when a fixtures has 'constructor: false'. The difference
 * with 'null' would be that null represents the absence of a method specified, which in the context of the constructor
 * is very different from specifying "no call".
 */
final class NoMethodCall implements MethodCallInterface
{
    /**
     * @inheritdoc
     */
    public function withArguments(array $arguments = null): self
    {
        $this->throwException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        $this->throwException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        $this->throwException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function getArguments()
    {
        $this->throwException(__METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return 'none';
    }

    private function throwException(string $method)
    {
        throw new \DomainException(
            sprintf(
                'By its nature, "%s()" should not be called.',
                $method
            )
        );
    }
}
