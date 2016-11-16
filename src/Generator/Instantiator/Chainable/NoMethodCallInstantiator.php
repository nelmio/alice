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
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory;

final class NoMethodCallInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritDoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return $fixture->getSpecs()->getConstructor() instanceof NoMethodCall;
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        try {
            return (new \ReflectionClass($fixture->getClassName()))->newInstanceWithoutConstructor();
        } catch (\ReflectionException $exception) {
            throw InstantiationExceptionFactory::create($fixture, 0, $exception);
        }
    }
}
