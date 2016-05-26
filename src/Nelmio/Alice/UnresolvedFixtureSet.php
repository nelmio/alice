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

final class UnresolvedFixtureSet
{
    /**
     * @var ParameterBag
     */
    private $parameters;
    
    /**
     * @var UnresolvedFixtureBag
     */
    private $fixtures;

    public function __construct(ParameterBag $parameters, UnresolvedFixtureBag $fixtures)
    {
        $this->parameters = $parameters;
        $this->fixtures = $fixtures;
    }

    public function __clone()
    {
        throw new \DomainException('Is not clonable.');
    }
}
