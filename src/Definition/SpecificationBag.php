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

namespace Nelmio\Alice\Definition;

/**
 * Value object containing all the elements necessary to define how the object described by the fixture must be
 * instantiated, hydrated and initialized.
 */
final class SpecificationBag
{
    /**
     * @var MethodCallInterface|null
     */
    private $constructor;
    
    /**
     * @var PropertyBag
     */
    private $properties;
    
    /**
     * @var MethodCallBag
     */
    private $calls;
    
    public function __construct(MethodCallInterface $constructor = null, PropertyBag $properties, MethodCallBag $calls)
    {
        $this->constructor = $constructor;
        $this->properties = $properties;
        $this->calls = $calls;
    }

    public function withConstructor(MethodCallInterface $constructor = null): self
    {
        $clone = clone $this;
        $clone->constructor = $constructor;

        return $clone;
    }

    /**
     * @return MethodCallInterface|null
     */
    public function getConstructor()
    {
        return $this->constructor;
    }
    
    public function getProperties(): PropertyBag
    {
        return $this->properties;
    }
    
    public function getMethodCalls(): MethodCallBag
    {
        return $this->calls;
    }

    /**
     * Creates a new instance to which the given specs have been merged. In case of conflicts, the existing values are
     * kept.
     */
    public function mergeWith(self $specs): self
    {
        $clone = clone $this;
        if (null === $clone->constructor) {
            $clone->constructor = $specs->constructor;
        }
        
        $clone->properties = $this->properties->mergeWith($specs->properties);
        $clone->calls = $this->calls->mergeWith($specs->calls);

        return $clone;
    }

    public function __clone()
    {
        if (null !== $this->constructor) {
            $this->constructor = clone $this->constructor;
        }
    }
}
