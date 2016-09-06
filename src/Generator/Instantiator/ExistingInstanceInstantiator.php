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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

/**
 * Check if the given fixture has already been instantiated and delegates the instantiation to the decorated
 * instantiator if that's not the case.
 */
final class ExistingInstanceInstantiator implements InstantiatorInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    public function __construct(InstantiatorInterface $decoratedInstantiator, ValueResolverInterface $resolver = null)
    {
        if ($resolver !== null && $decoratedInstantiator instanceof ValueResolverAwareInterface) {
            $decoratedInstantiator = $decoratedInstantiator->withResolver($resolver);
        }

        $this->instantiator = $decoratedInstantiator;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->instantiator, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function instantiate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet
    {
        if ($fixtureSet->getObjects()->has($fixture)) {
            return $fixtureSet;
        }

        return $this->instantiator->instantiate($fixture, $fixtureSet, $context);
    }
}
