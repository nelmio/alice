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

use Nelmio\Alice\Exception\FixtureNotFound;

final class ResolvedFixtureBag
{
    /**
     * @var mixed[]
     */
    private $fixtures = [];

    /**
     * @param mixed[] $fixtures Keys/values pair of parameters
     */
    public function __construct(array $fixtures = [])
    {
        foreach ($fixtures as $fixture) {
            if (false === $fixture instanceof ResolvedFixture) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected fixture to be a "%s" instance. Got "%s" instead',
                        is_object($fixture) ? get_class($fixture) : gettype($fixture)
                    )
                );
            }

            $this->fixtures[$fixture->getName()] = $fixture;
        }
    }

    public function with(ResolvedFixture $fixture): self
    {
        $clone = clone $this;
        $clone->fixtures[$fixture->getName()] = $fixture;

        return $clone;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->fixtures);
    }

    /**
     * @param string $name
     *
     * @throws FixtureNotFound
     *
     * @return ResolvedFixture
     */
    public function get(string $name)
    {
        if ($this->has($name)) {
            return $this->fixtures[$name];
        }

        throw new FixtureNotFound(sprintf('No fixture with the name "%s" found.', $name));
    }

    private function toArray(): array
    {
        return $this->fixtures;
    }
}
