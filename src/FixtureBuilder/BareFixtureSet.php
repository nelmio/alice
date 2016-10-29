<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\ParameterBag;

/**
 * Minimalist version of {@see \Nelmio\Alice\FixtureSet} containing only the loaded parameters and fixtures, i.e. does
 * not includes the injected parameters and objects.
 */
final class BareFixtureSet
{
    /**
     * @var ParameterBag
     */
    private $parameters;
    
    /**
     * @var FixtureBag
     */
    private $fixtures;

    public function __construct(ParameterBag $parameters, FixtureBag $fixtures)
    {
        $this->parameters = $parameters;
        $this->fixtures = $fixtures;
    }
    
    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }
    
    public function getFixtures(): FixtureBag
    {
        return $this->fixtures;
    }
}
