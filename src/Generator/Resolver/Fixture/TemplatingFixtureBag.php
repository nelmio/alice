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

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\FixtureNotFoundExceptionFactory;

/**
 * Bag containing fixtures and templates.
 */
final class TemplatingFixtureBag
{
    /**
     * @var FixtureBag
     */
    private $fixtures;

    /**
     * @var FixtureBag
     */
    private $templates;

    public function __construct()
    {
        $this->fixtures = new FixtureBag();
        $this->templates = new FixtureBag();
    }

    public function with(FixtureInterface $fixture): self
    {
        $clone = clone $this;
        if ($fixture instanceof TemplatingFixture && $fixture->isATemplate()) {
            $clone->templates = $clone->templates->with($fixture);
        } else {
            $clone->fixtures = $clone->fixtures->with($fixture);
        }

        return $clone;
    }

    public function has(string $id): bool
    {
        return $this->fixtures->has($id) || $this->templates->has($id);
    }

    public function hasTemplate(string $id): bool
    {
        return $this->templates->has($id);
    }

    public function get(string $id): FixtureInterface
    {
        if ($this->fixtures->has($id)) {
            return $this->fixtures->get($id);
        }

        if ($this->templates->has($id)) {
            return $this->templates->get($id);
        }

        throw FixtureNotFoundExceptionFactory::create($id);
    }

    public function getTemplate(string $id): FixtureInterface
    {
        if ($this->templates->has($id)) {
            return $this->templates->get($id);
        }

        throw FixtureNotFoundExceptionFactory::create($id);
    }

    public function getFixtures(): FixtureBag
    {
        return $this->fixtures;
    }
}
