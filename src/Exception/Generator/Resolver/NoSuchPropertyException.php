<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\FixtureInterface;

class NoSuchPropertyException extends UnresolvableValueException
{
    public static function createForFixture(FixtureInterface $fixture, FixturePropertyValue $value, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not find the property "%s" of the object "%s" (class: %s).',
                $value->getProperty(),
                $fixture->getId(),
                $fixture->getClassName()
            ),
            $code,
            $previous
        );
    }
}
