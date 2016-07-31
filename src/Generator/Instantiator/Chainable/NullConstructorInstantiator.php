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

use Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException;
use Nelmio\Alice\FixtureInterface;

final class NullConstructorInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritdoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return null === $fixture->getSpecs()->getConstructor();
    }

    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        $class = $fixture->getClassName();
        try {
            $constructRefl = new \ReflectionMethod($class, '__construct');

            if (false === $constructRefl->isPublic()) {
                throw new InstantiationException(
                    sprintf(
                        'Could not instantiate "%s", constructor is not public.',
                        $fixture->getId()
                    )
                );
            }

            if (0 === $constructRefl->getNumberOfRequiredParameters()) {
                return new $class();
            }

            throw new InstantiationException(
                sprintf(
                    'Could not instantiate "%s", constructor has mandatory parameters but no parameters has been given.',
                    $fixture->getId()
                )
            );
        } catch (\ReflectionException $exception) {
            // Thrown when __construct does not exist, i.e. is default constructor
            if (1 !== preg_match('/Method (.+)__construct\(.*\) does not exist/', $exception->getMessage())) {
                throw InstantiationException::create($fixture, $exception);
            }

            // Continue
        }

        return new $class();
    }
}
