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
    private $properties = [];

    public function with(MethodCallInterface $methodCall): self
    {
        $clone = clone $this;
        $clone->properties[$methodCall->getMethod()] = $methodCall;
        
        return $clone;
    }
}
