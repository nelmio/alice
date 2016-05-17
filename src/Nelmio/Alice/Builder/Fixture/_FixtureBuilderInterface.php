<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

use Nelmio\Alice\Fixture\UnresolvedFixtureBag;

interface FixtureBuilderInterface
{
    /**
     * @example
     *  $className = 'Nelmio\Entity\User'
     *  $name = 'user{1..10} (extends base_user)'
     *  $specs = [
     *      'username' => '<username()>'
     *  ]
     *
     * @param string $className
     * @param string $name
     * @param array  $specs
     *
     * @return UnresolvedFixtureBag
     */
    public function build(string $className, string $name, array $specs): UnresolvedFixtureBag;
}
