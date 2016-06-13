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

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Throwable\GenerationThrowable;

final class SimpleGenerator implements GeneratorInterface
{
    use NotClonableTrait;

    /**
     * @var ResolverInterface
     */
    private $resolver;
    
    /**
     * @var ObjectGeneratorInterface
     */
    private $generator;

    public function __construct(ResolverInterface $resolver, ObjectGeneratorInterface $generator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
    }

    /**
     * Generates a list of parameters and objects from the given set of data.
     *
     * @param FixtureSet $fixtureSet
     *
     * @throws GenerationThrowable
     *
     * @return ObjectSet Contains the parameters and objects built from the loaded and injected ones.
     */
    public function generate(FixtureSet $fixtureSet): ObjectSet
    {
        $resolvedFixtureSet = $this->resolver->resolve($fixtureSet);

        $objects = $resolvedFixtureSet->getObjects();
        $fixtures = $resolvedFixtureSet->getFixtures();
        foreach ($fixtures as $fixture) {
            $objects = $objects->mergeWith(
                $this->generator->generate($fixture, $resolvedFixtureSet, $objects)
            );
        }
        
        return new ObjectSet($resolvedFixtureSet->getParameters(), $objects);
    }
}
