<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

interface MethodInterface
{
    /**
     * tests whether this class can build an fixture with the given name
     *
     * @param  string  $name
     * @return boolean
     */
    public function canBuild($name);

    /**
     * builds an fixture from the given class, name, and spec
     *
     * @param  string $class
     * @param  string $name
     * @param  array  $spec
     * @return array
     */
    public function build($class, $name, array $spec);
}
