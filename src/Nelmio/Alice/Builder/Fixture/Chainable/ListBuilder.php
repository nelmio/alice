<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Chainable;

use Nelmio\Alice\Builder\ChainableBuilderInterface;
use Nelmio\Alice\FixtureSet;

final class ListBuilder implements ChainableBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $data): FixtureSet
    {
        // TODO: Implement build() method.
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $name): bool
    {
        // TODO: Implement canBuild() method.
    }
}
