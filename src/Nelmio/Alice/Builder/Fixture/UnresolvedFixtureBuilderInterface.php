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

use Nelmio\Alice\BuilderInterface;
use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\UnresolvedFixtureBag;

interface UnresolvedFixtureBuilderInterface
{
    /**
     * A more specific version of {@see Nelmio\Alice\BuilderInterface} dedicated to fixtures.
     *
     * @param UnresolvedFixtureBag $builtFixtures
     * @param string               $className FQCN (may contain flags)
     * @param string               $reference
     * @param array                $specs     Contains the list of property calls, constructor specification and method calls
     * @param FlagBag              $flags
     *
     * @throws BuilderInterface
     *
     * @return UnresolvedFixtureBag $builtFixtures with the new built fixtures.
     */
    public function build(UnresolvedFixtureBag $builtFixtures, string $className, string $reference, array $specs, FlagBag $flags): UnresolvedFixtureBag;
}
