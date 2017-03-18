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
use Nelmio\Alice\FixtureInterface;

final class NoCallerMethodCallInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritDoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        $constructor = $fixture->getSpecs()->getConstructor();

        return null !== $constructor && false === $constructor instanceof NoMethodCall && null === $constructor->getCaller();
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        list($class, $arguments) = [
            $fixture->getClassName(),
            array_values($fixture->getSpecs()->getConstructor()->getArguments())
        ];

        return new $class(...$arguments);
    }
}
