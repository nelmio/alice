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
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;

final class SimpleGenerator implements GeneratorInterface
{
    use NotClonableTrait;

    /**
     * @var FixtureSetResolverInterface
     */
    private $resolver;
    
    /**
     * @var ObjectGeneratorInterface
     */
    private $generator;

    public function __construct(FixtureSetResolverInterface $resolver, ObjectGeneratorInterface $generator)
    {
        $this->resolver = $resolver;
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function generate(FixtureSet $fixtureSet): ObjectSet
    {
        $resolvedFixtureSet = $this->resolver->resolve($fixtureSet);

        $objects = new ObjectBag();
        $fixtures = $resolvedFixtureSet->getFixtures();
        foreach ($fixtures as $fixture) {
            $objects = $this->generator->generate($fixture, $resolvedFixtureSet);
        }
        
        return new ObjectSet($resolvedFixtureSet->getParameters(), $objects);
    }
}
