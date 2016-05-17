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

final class ResolvedFixture
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $specs;

    /**
     * @var string
     */
    private $valueForCurrent;

    /**
     * @param string         $className
     * @param string         $name
     * @param Specifications $specs
     * @param string         $valueForCurrent - when <current()> is called, this value is used
     */
    public function __construct(string $className, string $name, Specifications $specs, string $valueForCurrent = null)
    {
        $this->className = $className;
        $this->name = $name;
        $this->specs = $specs;
        $this->valueForCurrent = $valueForCurrent;
    }

    public function extend(ResolvedFixture $extendedFixture): self
    {
        $clone = clone $this;

        if ($extendedFixture->getClassName() !== $clone->getClassName()) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Fixture "%s" extends "%s" but their class does not match. Got respectively "%s" and "%s"',
                    $clone->name,
                    $extendedFixture->getName(),
                    $clone->className,
                    $extendedFixture->getClassName()
                )
            );
        }

        $clone->specs = $clone->specs->extend($extendedFixture->getSpecs());

        return $clone;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpecs(): Specifications
    {
        return clone $this->specs;
    }

    /**
     * @return string|null
     */
    public function getValueForCurrent()
    {
        return $this->valueForCurrent;
    }
}
