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

use Nelmio\Alice\Exception\Fixtures\Builder\LogicException;
use Nelmio\Alice\Fixtures\Builder\ChainableBuilderInterface;
use Nelmio\Alice\Fixtures\ListName;
use Nelmio\Alice\Fixtures\RangedFixtureBuilder;

final class ListNameBuilder implements ChainableBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(string $className, string $name, array $specs): array
    {
        $names = $this->getNames($name);

        $fixtures = [];
        foreach ($names as $listName) {
            $fixtures[] = new RangedFixtureBuilder(
                $className,
                $listName->getName(),
                $specs,
                $listName->getFlags(),
                $listName->getCurrentValue()
            );
        }

        return $fixtures;
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $name, array &$matches = []): bool
    {
        return 1 === preg_match('/(.+)(\{([^,]+(?:\s*,\s*[^,]+)*)\})(?:.*)/', $name, $matches);
    }

    /**
     * @param string $name
     *
     * @throws LogicException
     *
     * @return ListName[] First value is the name with the flags and the second value the name
     *
     * @example
     *  'user_{alice, bob} (template)' => [
     *      ['user_alice', 'user_alice (template)']
     *      ['user_bob', 'user_bob (template)']
     *  ]
     */
    private function getNames(string $name): array
    {
        $matches = [];
        if (false === $this->canBuild($name, $matches)) {
            throw new LogicException(
                sprintf(
                    'As a chainable builder, "%s" should be called only if "::canBuild() returns true. Got false instead.',
                    __METHOD__
                )
            );
        }
        $nameParts = explode(',', $matches[3]);

        $names = [];
        foreach ($nameParts as $namePart) {
            $namePart = trim($namePart);
            $names[] = new ListName($matches[1].$namePart, $name, $namePart);
        }

        return $names;
    }
}
