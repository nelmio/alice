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

final class UnresolvedFixtureBag
{
    /**
     * @var mixed[]
     */
    private $fixtures = [];

    /**
     * @param UnresolvedFixture[] $fixtures
     */
    public function __construct(array $fixtures = [])
    {
        foreach ($fixtures as $fixture) {
            if (false === $fixture instanceof UnresolvedFixture) {
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

    /**
     * @param self $fixtures Existing fixtures with the same name are overridden by the new ones.
     *
     * @return self
     */
    public function with(self $fixtures): self
    {
        $clone = clone $this;
        $fixturesArray = $fixtures->toArray();
        foreach ($fixturesArray as $fixture) {
            $clone->fixtures[$fixture->getName()] = $fixture;
        }

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
     * @return UnresolvedFixture
     */
    public function get(string $name): UnresolvedFixture
    {
        if ($this->has($name)) {
            return $this->fixtures[$name];
        }

        throw new FixtureNotFound(sprintf('No fixture with the name "%s" found.', $name));
    }

    /**
     * @return UnresolvedFixture[]
     */
    public function toArray(): array
    {
        return $this->fixtures;
    }
}
