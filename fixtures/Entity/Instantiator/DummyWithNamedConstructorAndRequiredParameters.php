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

class DummyWithNamedConstructorAndRequiredParameters
{
    public static function namedConstruct(int $param)
    {
        $instance = new static();
        $instance->param = $param;

        return $instance;
    }
}
