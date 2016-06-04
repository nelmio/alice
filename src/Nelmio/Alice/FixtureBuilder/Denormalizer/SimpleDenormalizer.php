<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

final class SimpleBuilder implements DenormalizerInterface
{
    use NotClonableTrait;
    
    /**
     * @var ParameterBagDenormalizerInterface
     */
    private $parametersDenormalizer;

    /**
     * @var FixtureBagDenormalizerInterface
     */
    private $fixturesDenormalizer;

    public function __construct(ParameterBagDenormalizerInterface $parametersDenormalizer, FixtureBagDenormalizerInterface $fixturesDenormalizer)
    {
        $this->parametersDenormalizer = $parametersDenormalizer;
        $this->fixturesDenormalizer = $fixturesDenormalizer;
    }

    /**
     * Denormalizes the parsed data into a comprehensive collection of fixtures.
     *
     * @param array $data PHP data coming from the parser
     *
     * @throws DenormalizationThrowable
     *
     * @return BareFixtureSet Contains the loaded parameters and fixtures.
     */
    public function denormalize(array $data): BareFixtureSet
    {
        $parameters = $this->parametersDenormalizer->denormalize($data);
        
        unset($data['parameters']);
        $fixtures = $this->fixturesDenormalizer->denormalize($data);
        
        return new BareFixtureSet($parameters, $fixtures);
    }
}
