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

use Nelmio\Alice\UnresolvedFixtureInterface;

/**
 * Decorates UnresolvedFixture to add it flags.
 */
final class UnresolvedFixtureWithFlags implements UnresolvedFixtureInterface
{
    /**
     * @var UnresolvedFixture
     */
    private $fixture;

    /**
     * @var FlagBag
     */
    private $flags;

    public function __construct(string $reference, string $className, SpecificationBag $specs, FlagBag $flags)
    {
        $this->fixture = new UnresolvedFixture($reference, $className, $specs);
        $this->flags = $flags;
    }

    /**
     * @inheritdoc
     */
    public function getReference(): string
    {
        return $this->fixture->getReference();
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return $this->fixture->getClassName();
    }

    /**
     * @inheritdoc
     */
    public function getSpecs(): SpecificationBag
    {
        return $this->fixture->getSpecs();
    }

    public function withSpecs(SpecificationBag $specs): self
    {
        $clone = clone $this;
        $clone->fixture = $this->fixture->withSpecs($specs);
        
        return $clone;
    }
    
    public function getFlags(): FlagBag
    {
        return $this->flags;
    }
    
    public function __clone()
    {
        throw new \DomainException('Is not clonable.');
    }
}
