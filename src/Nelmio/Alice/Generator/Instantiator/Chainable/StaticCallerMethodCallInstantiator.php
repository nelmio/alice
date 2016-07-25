<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException;
use Nelmio\Alice\FixtureInterface;

final class StaticCallerMethodCallInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritDoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        $constructor = $fixture->getSpecs()->getConstructor();

        return null !== $constructor && false === $constructor instanceof NoMethodCall && $constructor->getCaller() instanceof StaticReference;
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        $constructor = $fixture->getSpecs()->getConstructor();
        list($factory, $method, $arguments) = [
            $constructor->getCaller()->getReference(),
            $constructor->getMethod(),
            $constructor->getArguments()
        ];

        try {
            return $factory::$method($arguments);
        } catch (\Throwable $thrownable) {
            throw InstantiationException::create($fixture, $thrownable);
        }
    }
}
