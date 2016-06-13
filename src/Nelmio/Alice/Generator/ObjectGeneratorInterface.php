<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\Throwable\GenerationThrowable;

/**
 * More specific version of {@see Nelmio\Alice\GeneratorInterface}.
 */
interface ObjectGeneratorInterface
{
    /**
     * Generates a fixture.
     *
     * @param FixtureInterface   $fixture    Fixture to generate
     * @param ResolvedFixtureSet $fixtureSet List of fixtures being generated
     * @param ObjectBag          $objects    Generated/Injected objects
     *
     * @throws GenerationThrowable
     *
     * @return ObjectBag New instance of $objects with the objects generated when generating $fixture.
     */
    public function generate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, ObjectBag $objects): ObjectBag;
}
