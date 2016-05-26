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

final class FixtureSet
{
    /**
     * @var ParameterBag
     */
    private $parameters;
    
    /**
     * @var FixtureBag
     */
    private $fixtures;

    public function __construct(ParameterBag $parameters, FixtureBag $fixtures)
    {
        $this->parameters = $parameters;
        $this->fixtures = $fixtures;
    }

    public function __clone()
    {
        throw new \DomainException('Is not clonable.');
    }
}
