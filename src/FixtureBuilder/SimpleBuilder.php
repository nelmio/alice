<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;

final class SimpleBuilder implements FixtureBuilderInterface
{
    use NotClonableTrait;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @inheritdoc
     */
    public function build(array $data, array $parameters = [], array $objects = []): FixtureSet
    {
        $bareFixtureSet = $this->denormalizer->denormalize($data);
        
        return new FixtureSet(
            $bareFixtureSet->getParameters(),
            new ParameterBag($parameters),
            $bareFixtureSet->getFixtures(),
            new ObjectBag($objects)
        );
    }
}
