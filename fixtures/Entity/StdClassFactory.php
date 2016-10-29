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

class StdClassFactory
{
    /**
     * Creates an stdClass instance with the given attributes. For example:
     *
     * $std = $factory->create(['foo' => 'bar', 'ping' => 'pong']);
     *
     * is equivalent to:
     *
     * $std = new \stdClass();
     * $std->foo = 'bar';
     * $std->ping = 'pong';
     *
     * @param array $attributes
     *
     * @return \stdClass
     */
    public static function create(array $attributes = []): \stdClass
    {
        $instance = new \stdClass();
        foreach ($attributes as $attribute => $value) {
            $instance->$attribute = $value;
        }

        return $instance;
    }
}
