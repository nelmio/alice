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

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\InstantiationThrowable;

class InstantiationException extends \RuntimeException implements InstantiationThrowable
{
    public static function create(FixtureInterface $fixture, int $code = 0, \Throwable $previous = null): self
    {
        return new static(
            sprintf(
                'Could not instantiate fixture "%s".',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }

    final public static function createForNonPublicConstructor(FixtureInterface $fixture): self
    {
        return new static(
            sprintf(
                'Could not instantiate "%s", constructor is not public.',
                $fixture->getId()
            )
        );
    }

    final public static function createForConstructorIsMissingMandatoryParameters(FixtureInterface $fixture): self
    {
        return new static(
            sprintf(
                'Could not instantiate "%s", constructor has mandatory parameters but no parameters has been given.',
                $fixture->getId()
            )
        );
    }

    final public static function createForCouldNotGetConstructorData(
        FixtureInterface $fixture,
        int $code = 0,
        \Throwable $previous = null
    ): self
    {
        return new static(
            sprintf(
                'Could not get the necessary data on the constructor to instantiate "%s"..',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }

    /**
     * @param string $class
     * @param object       $instance
     *
     * @return static
     */
    final public static function createForInvalidInstanceType(string $class, $instance): self
    {
        return new static(
            sprintf(
                'Instantiated fixture was expected to be an instance of "%s". Got "%s" instead.',
                $class,
                get_class($instance)
            )
        );
    }
}
