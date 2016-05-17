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

use Nelmio\Alice\Builder\Fixture\ChainableFixtureBuilderInterface;
use Nelmio\Alice\Builder\FlagParser;
use Nelmio\Alice\Fixture\UnresolvedFixtureBag;

final class SimpleBuilder implements ChainableFixtureBuilderInterface
{
    /**
     * @var FlagParser
     */
    private $flagParser;

    public function __construct(FlagParser $flagParser)
    {
        $this->flagParser = $flagParser;
    }

    /**
     * @inheritdoc
     */
    public function build(string $className, string $name, array $specs): UnresolvedFixtureBag
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
