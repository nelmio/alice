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
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class InstantiatorRegistry implements InstantiatorInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ChainableInstantiatorInterface[]
     */
    private $instantiators;

    /**
     * @param ChainableInstantiatorInterface[] $instantiators
     * @param ValueResolverInterface|null      $resolver
     */
    public function __construct(array $instantiators, ValueResolverInterface $resolver = null)
    {
        $this->instantiators = (
            function (ValueResolverInterface $resolver = null, ChainableInstantiatorInterface ...$instantiators) {
                if (null === $resolver) {
                    return $instantiators;
                }

                foreach ($instantiators as $index => $instantiator) {
                    if ($instantiator instanceof ValueResolverAwareInterface) {
                        $instantiators[$index] = $instantiator->withResolver($resolver);
                    }
                }

                return $instantiators;
            }
        )($resolver, ...$instantiators);
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->instantiators, $resolver);
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
