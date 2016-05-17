<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\Builder\Fixture\FixtureBuilderInterface;
use Nelmio\Alice\Fixture\UnresolvedFixtureBag;

/**
 * @internal
 * @final
 */
class UnresolvedFixtureBuilder
{
    /**
     * @var FixtureBuilderInterface
     */
    private $builder;

    public function __construct(FixtureBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Builds the fixtures bag from the passed data.
     *
     * @param array $data
     *
     * @throws \TypeError
     *
     * @return UnresolvedFixtureBag
     */
    public function build(array $data): UnresolvedFixtureBag
    {
        unset($data['include']);
        unset($data['parameters']);

        $fixtures = new UnresolvedFixtureBag();
        foreach ($data as $className => $fixturesData) {
            if (false === is_array($fixturesData)) {
                throw new \TypeError(
                    sprintf(
                        'Fixtures elements must be arrays. Found "%s" instead for fixture "%s".',
                        gettype($fixturesData),
                        $className
                    )
                );
            }

            $fixtures = $fixtures->with(
                $this->buildFixtures($className, $fixturesData)
            );
        }

        return $fixtures;
    }

    private function buildFixtures(string $className, array $fixturesData): UnresolvedFixtureBag
    {
        $builtFixtures = new UnresolvedFixtureBag();
        foreach ($fixturesData as $name => $specs) {
            if (false === is_array($specs)) {
                throw new \TypeError(
                    sprintf(
                        'Fixtures specs must be arrays. Found "%s" instead for fixture "%s::%s"',
                        gettype($fixturesData),
                        $className
                    )
                );
            }

            $builtFixtures = $builtFixtures->with(
                $this->builder->build($className, $name, $specs)
            );
        }

        return $builtFixtures;
    }
}
