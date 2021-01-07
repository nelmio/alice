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

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\FixtureWithFlagsInterface;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * Decorates a fixture to add it flags.
 */
final class SimpleFixtureWithFlags implements FixtureWithFlagsInterface
{
    /**
     * @var FixtureInterface
     */
    private $fixture;

    /**
     * @var FlagBag
     */
    private $flags;

    public function __construct(FixtureInterface $fixture, FlagBag $flags)
    {
        if ($fixture->getId() !== $flags->getKey()) {
            throw InvalidArgumentExceptionFactory::createForFlagBagKeyMismatch($fixture, $flags);
        }

        $this->fixture = clone $fixture;
        $this->flags = $flags;
    }
    
    public function getId(): string
    {
        return $this->fixture->getId();
    }
    
    public function getClassName(): string
    {
        return $this->fixture->getClassName();
    }
    
    public function getSpecs(): SpecificationBag
    {
        return $this->fixture->getSpecs();
    }
    
    public function getValueForCurrent()
    {
        return $this->fixture->getValueForCurrent();
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
}
