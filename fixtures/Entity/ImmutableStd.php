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

namespace Nelmio\Alice\Entity;

use function Nelmio\Alice\deep_clone;

class ImmutableStd
{
    private $properties;

    public function __construct(array $properties)
    {
        $this->properties = deep_clone($properties);
    }

    public function __set(string $name, $value)
    {
        $this->properties[$name] = deep_clone($value);
    }

    public function __get(string $name)
    {
        return deep_clone($name);
    }
}
