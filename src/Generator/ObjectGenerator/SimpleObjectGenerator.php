<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectBag;

final class SimpleObjectGenerator implements ObjectGeneratorInterface
{
    use NotClonableTrait;
    
    /**
     * @var InstantiatorInterface
     */
    private $instantiator;
    
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var CallerInterface
     */
    private $caller;

    public function __construct(
        ValueResolverInterface $valueResolver,
        InstantiatorInterface $instantiator,
        HydratorInterface $hydrator,
        CallerInterface $caller
    ) {
        if ($valueResolver instanceof ObjectGeneratorAwareInterface) {
            $valueResolver = $valueResolver->withObjectGenerator($this);
        }

        if ($instantiator instanceof ValueResolverAwareInterface) {
            $instantiator = $instantiator->withValueResolver($valueResolver);
        }

        if ($hydrator instanceof ValueResolverAwareInterface) {
            $hydrator = $hydrator->withValueResolver($valueResolver);
        }

        if ($caller instanceof ValueResolverAwareInterface) {
            $caller = $caller->withValueResolver($valueResolver);
        }

        $this->instantiator = $instantiator;
        $this->hydrator = $hydrator;
        $this->caller = $caller;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ObjectBag
    {
        if ($context->isFirstPass()) {
            return $this->instantiator->instantiate($fixture, $fixtureSet, $context)->getObjects();
        }
        $fixtureSet = $this->completeObject($fixture, $fixtureSet, $context);

        return $fixtureSet->getObjects();
    }

    private function completeObject(
        FixtureInterface $fixture,
        ResolvedFixtureSet $set,
        GenerationContext $context
    ): ResolvedFixtureSet
    {
        $instantiatedObject = $set->getObjects()->get($fixture);

        $set = $this->hydrator->hydrate($instantiatedObject, $set, $context);
        $hydratedObject = $set->getObjects()->get($fixture);

        return $this->caller->doCallsOn($hydratedObject, $set);
    }
}
