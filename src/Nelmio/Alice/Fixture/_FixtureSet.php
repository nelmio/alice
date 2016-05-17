<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

final class FixtureSet
{
    /**
     * @var ResolvedFixtureBag
     */
    private $fixtures;

    /**
     * @var ParameterBag
     */
    private $parameters;

    public function __construct(ParameterBag $parameters, ResolvedFixtureBag $fixtures)
    {
        $this->parameters = $parameters;
        $this->fixtures = $fixtures;
    }

    public function getFixtures(): ResolvedFixtureBag
    {
        return $this->fixtures;
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }
}
