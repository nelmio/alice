<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

class ReflectionWithoutConstructor implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
    {
        if (!$fixture->shouldUseConstructor()) {
            return true;
        }

        try {
            $reflectionMethod = new \ReflectionMethod($fixture->getClass(), '__construct');

            return (
                false === $reflectionMethod->isPublic()
                && '__construct' === $fixture->getConstructorMethod()
            );
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        $reflClass = new \ReflectionClass($fixture->getClass());
        if ($fixture->shouldUseConstructor()
            && null !== $fixture->getConstructorMethod()
            && [] !== $fixture->getConstructorArgs()
        ) {
            @trigger_error(
                'Using a private or protected constructor is deprecated since 2.3.0 and will be removed in '
                .'Alice 3.0.0.',
                E_USER_DEPRECATED
            );
        }

        return $reflClass->newInstanceWithoutConstructor();
    }
}
