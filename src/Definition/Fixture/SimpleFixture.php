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

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentExceptionFactory;

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
     * @var SpecificationBag
     */
    private $specs;

    /**
     * @var string|int|FixtureInterface
     */
    private $valueForCurrent;

    /**
     * @param string                           $id
     * @param string                           $className
     * @param SpecificationBag                 $specs
     * @param string|int|FixtureInterface|null $valueForCurrent
     */
    public function __construct(string $id, string $className, SpecificationBag $specs, $valueForCurrent = null)
    {
        $this->id = $id;
        $this->className = $className;
        $this->specs = $specs;
        $this->valueForCurrent = $valueForCurrent;
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
        return $this->specs;
    }

    /**
     * @inheritdoc
     */
    public function getValueForCurrent()
    {
        if (null === $this->valueForCurrent) {
            throw NoValueForCurrentExceptionFactory::create($this);
        }

        return $this->valueForCurrent;
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
}
