<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\BuilderInterface;
use Nelmio\Alice\Fixtures\Definition\FixtureDefinition;
use Nelmio\Alice\Fixtures\Definition\UnresolvedFixtureDefinition;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\NewFixture;
use Nelmio\Alice\Throwable\Fixtures\BuilderThrowable;

final class NewBuilder implements BuilderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param UnresolvedFixtureDefinition[] $definitions Keys are the definition name
     */
    public function build(array $definitions): array
    {
        $fixtures = [];
        foreach ($definitions as $definition) {
            $fixtures[] = $this->buildDefinition($definition);
        }
    }

    private function buildDefinition(UnresolvedFixtureDefinition $definition, array &$definitions): NewFixture
    {
        $definition->
    }
}
