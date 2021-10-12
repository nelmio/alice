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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationExceptionFactory;
use ReflectionException;
use ReflectionMethod;

final class NullConstructorInstantiator extends AbstractChainableInstantiator
{
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return null === $fixture->getSpecs()->getConstructor();
    }
    
    protected function createInstance(FixtureInterface $fixture)
    {
        $class = $fixture->getClassName();

        try {
            $constructRefl = new ReflectionMethod($class, '__construct');

            if (false === $constructRefl->isPublic()) {
                throw InstantiationExceptionFactory::createForNonPublicConstructor($fixture);
            }

            if (0 === $constructRefl->getNumberOfRequiredParameters()) {
                return new $class();
            }

            throw InstantiationExceptionFactory::createForConstructorIsMissingMandatoryParameters($fixture);
        } catch (ReflectionException $exception) {
            // Thrown when __construct does not exist, i.e. is default constructor
            if (1 !== preg_match('/Method (.+)__construct\(.*\) does not exist/', $exception->getMessage())) {
                throw InstantiationExceptionFactory::createForCouldNotGetConstructorData($fixture, 0, $exception);
            }

            // Continue
        }

        return new $class();
    }
}
