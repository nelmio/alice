<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException;
use Nelmio\Alice\FixtureInterface;

final class StaticFactoryInstantiator extends AbstractChainableInstantiator
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
        list($class, $factory, $method, $arguments) = [
            $fixture->getClassName(),
            $constructor->getCaller()->getId(),
            $constructor->getMethod(),
            $constructor->getArguments()
        ];

        if (null === $arguments) {
            $arguments = [];
        }

        $instance = $factory::$method(...$arguments);
        if (false === $instance instanceof $class) {
            throw new InstantiationException(
                sprintf(
                    'Instantiated fixture was expected to be an instance of "%s". Got "%s" instead.',
                    $class,
                    get_class($instance)
                )
            );
        }

        return $instance;
    }
}
