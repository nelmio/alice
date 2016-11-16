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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\IsAServiceTrait;

final class SimpleDenormalizer implements DenormalizerInterface
{
    use IsAServiceTrait;

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
     * @inheritdoc
     */
    public function denormalize(array $data): BareFixtureSet
    {
        $parameters = $this->parametersDenormalizer->denormalize($data);

        unset($data['parameters']);
        $fixtures = $this->fixturesDenormalizer->denormalize($data);

        return new BareFixtureSet($parameters, $fixtures);
    }
}
