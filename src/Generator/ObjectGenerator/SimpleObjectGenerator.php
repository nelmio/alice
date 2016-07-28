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
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
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
     * @var PopulatorInterface
     */
    private $populator;

    /**
     * @var CallerInterface
     */
    private $caller;

    public function __construct(
        InstantiatorInterface $instantiator,
        PopulatorInterface $populator,
        CallerInterface $caller
    ) {
        $this->instantiator = $instantiator;
        $this->populator = $populator;
        $this->caller = $caller;
    }

    /**
     * @inheritdoc
     */
    public function generate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ObjectBag
    {
        $fixtureSet = $this->instantiator->instantiate($fixture, $fixtureSet);
        $unpopulatedObject = $fixtureSet->getObjects()->get($fixture);
        
        $fixtureSet = $this->populator->populate($unpopulatedObject, $fixtureSet);
        $populatedObject = $fixtureSet->getObjects()->get($fixture);
        
        $fixtureSet = $this->caller->doCallsOn($populatedObject, $fixtureSet);
        
        return $fixtureSet->getObjects();
    }
}
