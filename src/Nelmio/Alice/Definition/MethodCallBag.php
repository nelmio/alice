<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

final class MethodCallBag
{
    /**
     * @var MethodCallInterface[]
     */
    private $methodCalls = [];

    public function with(MethodCallInterface $methodCall): self
    {
        $clone = clone $this;
        $clone->methodCalls[$methodCall->__toString()] = $methodCall;

        return $clone;
    }

    /**
     * Creates a new instance to which the given properties have been merged. In case of conflicts, the existing values
     * are overridden.
     *
     * @param self $methodCallsBag
     *
     * @return self
     */
    public function mergeWith(self $methodCallsBag): self
    {
        $clone = clone $this;
        foreach ($methodCallsBag->methodCalls as $stringValue => $methodCall) {
            $clone->methodCalls[$stringValue] = $methodCall;
        }

        return $clone;
    }
}
