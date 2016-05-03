<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Throwable\Fixtures\BuilderThowable;

interface BuilderInterface
{
    /**
     * Builds a fixture definition.
     *
     * @param  string $className FQCN
     * @param  string $name
     * @param  array  $specs
     *
     * @example
     *  with:
     *
     * ```yaml
     *  Nelmio\Alice\support\models\User:
     *      user{1..10}:
     *          username: <username()>
     *          fullname: <firstName()> <lastName()>
     * ```
     *
     *  you will have:
     *  $className: 'Nelmio\Alice\support\models\User'
     *  $name: 'user{1..10}'
     *  $spec: [
     *      'username' => '<username()>'
     *      'fullname' => '<firstName()> <lastName()>'
     *  ]
     *
     * @throws BuilderThowable
     *
     * @return Fixture[]
     */
    public function build(string $className, string $name, array $specs): array;
}
