<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Chainable;

use Nelmio\Alice\Fixtures\Builder\ChainableBuilderInterface;
use Nelmio\Alice\Fixtures\FixtureBuilder;

final class SimpleNameBuilder implements ChainableBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(string $className, string $name, array $specs): array
    {
        return [new FixtureBuilder($className, $name, $specs, $name)];
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $name): bool
    {
        return true;
    }
}
