<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

final class UnresolvedFixtureBag
{
    /**
     * @var UnresolvedFixture[]
     */
    private $fixtures = [];
    
    public function with(UnresolvedFixture $fixture): self
    {
        $clone = clone $this;
        $clone->fixtures[$fixture->getClassName().$fixture->getReference()] = $fixture;
        
        return $clone;
    }
}
