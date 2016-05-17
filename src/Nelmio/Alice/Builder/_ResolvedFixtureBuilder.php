<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\Fixture\ResolvedFixtureBag;

/**
 * @internal
 * @final
 */
class ResolvedFixtureBuilder
{
    /**
     * @var UnresolvedFixtureBuilder
     */
    private $builder;

    public function __construct(UnresolvedFixtureBuilder $unresolvedFixtureBuilder)
    {
        $this->builder = $unresolvedFixtureBuilder;
    }

    public function build(array $data): ResolvedFixtureBag
    {
        $unresolvedFixtures = $this->builder->build($data);

        return (new FixturesResolver($unresolvedFixtures))->resolve();
    }
}
