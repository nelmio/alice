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
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * Decorates SimpleFixtureWithFlags to provide helpers regarding templates related flags.
 */
final class TemplatingFixture implements FixtureWithFlagsInterface
{
    /**
     * @var FixtureWithFlagsInterface
     */
    private $fixture;

    /**
     * @var Templating
     */
    private $templating;

    public function __construct(FixtureWithFlagsInterface $fixture)
    {
        $this->fixture = clone $fixture;
        $this->templating = new Templating($fixture);
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

    public function isATemplate(): bool
    {
        return $this->templating->isATemplate();
    }

    public function extendsFixtures(): bool
    {
        return $this->templating->extendsFixtures();
    }

    /**
     * @return FixtureReference[] List of fixture ids that the fixture extends.
     */
    public function getExtendedFixturesReferences(): array
    {
        return $this->templating->getExtendedFixtures();
    }
    
    public function getFlags(): FlagBag
    {
        return $this->fixture->getFlags();
    }
}
