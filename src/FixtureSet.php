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

namespace Nelmio\Alice;

/**
 * Value objects containing the loaded parameters, fixtures, injected parameters and injected objects.
 */
final class FixtureSet
{
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

    public function getLoadedParameters(): ParameterBag
    {
        return $this->loadedParameters;
    }

    public function getInjectedParameters(): ParameterBag
    {
        return $this->injectedParameters;
    }

    public function getFixtures(): FixtureBag
    {
        return $this->fixtures;
    }

    public function getObjects(): ObjectBag
    {
        return $this->injectedObjects;
    }
}
