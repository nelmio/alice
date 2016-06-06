<?php

/**
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

/**
 * Value object containing all the elements necessary to define how the object described by the fixture must be
 * instantiated, populated and initialized.
 */
final class SpecificationBag
{
    /**
     * @var MethodCallDefinition|null
     */
    private $constructor;
    
    /**
     * @var PropertyDefinitionBag
     */
    private $properties;
    
    /**
     * @var MethodCallDefinition
     */
    private $calls;

    /**
     * @param MethodCallDefinition|null $constructor
     * @param PropertyDefinitionBag     $properties
     * @param MethodCallDefinitionBag   $calls
     */
    public function __construct(
        MethodCallDefinition $constructor = null,
        PropertyDefinitionBag $properties,
        MethodCallDefinitionBag $calls
    ) {
        $this->constructor = $constructor;
        $this->properties = $properties;
        $this->calls = $calls;
    }

    /**
     * Creates a new instance to which the given specs have been merged. In case of conflicts, the existing values are
     * overridden.
     * 
     * @param self $specs
     *
     * @return self
     */
    public function mergeWith(self $specs): self
    {
        //TODO
    }

    public function __clone()
    {
        if (null !== $this->constructor) {
            $this->constructor = clone $this->constructor;
        }
        
        $this->properties = clone $this->properties;
        $this->calls = clone $this->calls;
    }
}
