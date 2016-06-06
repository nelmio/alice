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
use Nelmio\Alice\NotClonableTrait;

/**
 * Decorates FixtureWithFlags to provide helpers regarding templates related flags.
 */
final class TemplatingFixture implements FixtureInterface
{
    use NotClonableTrait;
    
    /**
     * @var FixtureWithFlags
     */
    private $fixture;

    /**
     * @var Templating
     */
    private $templating;

    public function __construct(FixtureWithFlags $fixture)
    {
        $this->fixture = $fixture;
        $this->templating = new Templating($fixture->getClassName(), $fixture->getFlags());
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

    public function isATemplate(): bool
    {
        return $this->templating->isATemplate();
    }

    public function extendsFixtures(): bool
    {
        return $this->templating->extendsFixtures();
    }

    /**
     * @return string[] List of fixture ids that the fixture extends.
     */
    public function getExtendedFixtures(): array
    {
        return $this->templating->getExtendedFixtures();
    }
}
