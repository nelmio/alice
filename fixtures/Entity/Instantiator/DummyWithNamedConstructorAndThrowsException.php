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

namespace Nelmio\Alice\Entity\Instantiator;

use DomainException;

class DummyWithNamedConstructorAndThrowsException
{
    public static function namedConstruct(): void
    {
        throw new DomainException();
    }
}
