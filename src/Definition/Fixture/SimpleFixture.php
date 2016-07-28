<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * Minimalist implementation of a fixture.
 */
final class SimpleFixture implements FixtureInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $specs;

    public function __construct(string $id, string $className, SpecificationBag $specs)
    {
        $this->id = $id;
        $this->className = $className;
        $this->specs = $specs;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @inheritdoc
     */
    public function getSpecs(): SpecificationBag
    {
        return clone $this->specs;
    }

    /**
     * @inheritdoc
     */
    public function withSpecs(SpecificationBag $specs): self
    {
        $clone = clone $this;
        $clone->specs = $specs;
        
        return $clone;
    }
    
    public function __clone()
    {
        $this->specs = clone $this->specs;
    }
}
