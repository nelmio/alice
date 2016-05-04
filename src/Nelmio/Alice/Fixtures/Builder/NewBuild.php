<?php

/**
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
use Nelmio\Alice\Fixtures\Definition\UnresolvedRangedFixtureDefinition;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\NewFixture;
use Nelmio\Alice\Fixtures\NewFixtureWithCurrent;
use Nelmio\Alice\Throwable\Fixtures\BuilderThrowable;

final class NewBuild
{
    /**
     * @var UnresolvedFixtureDefinition[]
     */
    private $definitions = [];

    private $fixtures = [];

    private $templates = [];

    /**
     * @param UnresolvedFixtureDefinition[] $definitions
     */
    public function __construct(array $definitions)
    {
        foreach ($this->definitions as $definition) {
            $this->definitions[$definition->getName()] = $definitions;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param UnresolvedFixtureDefinition[] $definitions Keys are the definition name
     */
    public function build(): array
    {
        $fixtures = [];
        foreach ($this->definitions as $definition) {
            $fixtures[$definition->getName()] = $this->buildDefinition($definition);
        }
    }

    private function buildDefinition(UnresolvedFixtureDefinition $definition): NewFixture
    {
        if ($definition->extendTemplates()) {
            $templates = $definition->getExtendedTemplates();
        }

        if ($definition->isTemplate()) {
            //TODO
        }

        if ($definition instanceof UnresolvedRangedFixtureDefinition) {
            return new NewFixtureWithCurrent(
                $definition->getClassName(),
                $definition->getName(),
                $definition->getSpecs(),
                $definition->getValueForCurrent()
            );
        }

        return new NewFixture(
            $definition->getClassName(),
            $definition->getName(),
            $definition->getSpecs()
        );
    }

    private function buildWithTemplates()
    {
        //TODO
    }
}
