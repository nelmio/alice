<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Exception\Generator\Instantiator\InstantiatorNotFoundException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotClonableTrait;

final class InstantiatorRegistry implements InstantiatorInterface
{
    use NotClonableTrait;

    /**
     * @var ChainableInstantiatorInterface[]
     */
    private $instantiators;

    /**
     * @param ChainableInstantiatorInterface[] $instantiators
     */
    public function __construct(array $instantiators)
    {
        $this->instantiators = (function (ChainableInstantiatorInterface ...$instantiators) { return $instantiators; })(...$instantiators);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InstantiatorNotFoundException
     */
    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        foreach ($this->instantiators as $instantiator) {
            if ($instantiator->canInstantiate($fixture)) {
                return $instantiator->instantiate($fixture, $fixtureSet);
            }
        }

        throw InstantiatorNotFoundException::create($fixture);
    }
}
