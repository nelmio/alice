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

use Nelmio\Alice\BuilderInterface;
use Nelmio\Alice\UnresolvedFixtureSet;

final class DefaultBuilder implements BuilderInterface
{
    /**
     * @var UnresolvedFixtureBagBuilderInterface
     */
    private $fixturesBuilder;

    /**
     * @var ParameterBagBuilderInterface
     */
    private $parametersBuilder;

    public function __construct(ParameterBagBuilderInterface $parametersBuilder, UnresolvedFixtureBagBuilderInterface $fixturesBuilder)
    {
        $this->parametersBuilder = $parametersBuilder;
        $this->fixturesBuilder = $fixturesBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build(array $fixtures): UnresolvedFixtureSet
    {
        $parameters = $this->parametersBuilder->build($fixtures);
        
        unset($parameters['parameters']);
        $fixtures = $this->fixturesBuilder->build($fixtures);
        
        return new UnresolvedFixtureSet($parameters, $fixtures);
    }
}
