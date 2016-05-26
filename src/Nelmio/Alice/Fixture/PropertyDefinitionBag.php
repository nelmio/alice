<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

final class PropertyDefinitionBag
{
    /**
     * @var PropertyDefinition[]
     */
    private $properties = [];

    public function with(PropertyDefinition $property): self
    {
        $clone = clone $this;
        $clone->properties[$property->getName()] = $property;
        
        return $clone;
    }
}
