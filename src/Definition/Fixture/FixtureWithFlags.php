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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * Decorates a fixture to add it flags.
 */
final class FixtureWithFlags implements FixtureInterface
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected the fixture ID and the flags key to be the same. Got "%s" and "%s" instead.',
                    $fixture->getId(),
                    $flags->getKey()
                )
            );
        }

        $this->fixture = clone $fixture;
        $this->flags = $flags;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->fixture->getId();
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

    /**
     * @inheritdoc
     */
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
