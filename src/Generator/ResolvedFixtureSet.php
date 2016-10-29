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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;

/**
 * Another version {@see \Nelmio\Alice\FixtureSet} where loaded parameters have been resolved and injected parameters
 * have been merged in the process; And the fixtures flags have been resolved (i.e. fixtures no longer have flags
 * although their specs may still have some).
 */
final class ResolvedFixtureSet
{
    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * @var FixtureBag
     */
    private $fixtures;

    /**
     * @var ObjectBag
     */
    private $objects;

    public function __construct(
        ParameterBag $parameters,
        FixtureBag $fixtures,
        ObjectBag $injectedObjects
    ) {
        $this->parameters = $parameters;
        $this->fixtures = $fixtures;
        $this->objects = $injectedObjects;
    }

    public function withParameters(ParameterBag $parameters): self
    {
        $clone = clone $this;
        $clone->parameters = $parameters;

        return $clone;
    }

    public function withFixtures(FixtureBag $fixtures): self
    {
        $clone = clone $this;
        $clone->fixtures = $fixtures;

        return $clone;
    }

    public function withObjects(ObjectBag $objects): self
    {
        $clone = clone $this;
        $clone->objects = $objects;

        return $clone;
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    public function getFixtures(): FixtureBag
    {
        return $this->fixtures;
    }

    public function getObjects(): ObjectBag
    {
        return $this->objects;
    }
}
