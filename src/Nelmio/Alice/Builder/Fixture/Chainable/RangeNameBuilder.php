<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Chainable;

use Nelmio\Alice\Exception\Fixtures\Builder\LogicException;
use Nelmio\Alice\Fixtures\Definition\Builder\ChainableDefinitionBuilderInterface;
use Nelmio\Alice\Fixtures\Definition\UnresolvedRangedFixtureDefinition;
use Nelmio\Alice\Fixtures\RangeName;

final class RangeNameBuilder implements ChainableDefinitionBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(string $className, string $name, array $specs): array
    {
        $range = $this->getRange($name);

        $fixtures = [];
        for ($currentIndex = $range->getFrom(); $currentIndex <= $range->getTo(); $currentIndex++) {
            $fixtureName = sprintf("%s%s", $range->getName(), $currentIndex);

            $fixtures[] = new UnresolvedRangedFixtureDefinition(
                $className,
                $fixtureName,
                $specs,
                $name,
                (string) $currentIndex
            );
        }

        return $fixtures;
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $name, array &$matches = []): bool
    {
        return 1 === preg_match('/(.*)\{([0-9]+)(?:\.{2})([0-9]+)\}/', $name, $matches);
    }

    /**
     * @param string $name
     *
     * @throws LogicException
     *
     * @return RangeName
     */
    private function getRange(string $name): RangeName
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
        $from = $matches[2];
        $to = $matches[3];

        if ($from > $to) {
            list($from, $to) = [$to, $from];
        }

        return new RangeName($matches[1], $from, $to);
    }
}
