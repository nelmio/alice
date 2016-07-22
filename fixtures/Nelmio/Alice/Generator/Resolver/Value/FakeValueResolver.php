<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeValueResolver implements ValueResolverInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function resolve(
        $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = []): ResolvedValueWithFixtureSet
    {
        $this->__call();
    }
}
