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

/**
 * Value objects containing the loaded parameters, fixtures and the injected parameters, objects.
 */
final class FixtureSet
{
    use NotClonableTrait;
    
    /**
     * @var ParameterBag
     */
    private $loadedParameters;
    
    /**
     * @var ParameterBag
     */
    private $injectedParameters;
    
    /**
     * @var FixtureBag
     */
    private $fixtures;
    
    /**
     * @var ObjectBag
     */
    private $injectedObjects;

    public function __construct(
        ParameterBag $loadedParameters,
        ParameterBag $injectedParameters,
        FixtureBag $fixtures,
        ObjectBag $injectedObjects
    ) {
        $this->loadedParameters = $loadedParameters;
        $this->injectedParameters = $injectedParameters;
        $this->fixtures = $fixtures;
        $this->injectedObjects = $injectedObjects;
    }
}
