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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\ParameterBagDenormalizerInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;

final class SimpleParameterBagDenormalizer implements ParameterBagDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * {@inheritdoc}
     *
     * @param array $data Full set of parsed data, will look for the parameter subset itself.
     */
    public function denormalize(array $data): ParameterBag
    {
        if (false === array_key_exists('parameters', $data)
            || null === $fixturesParameters = $data['parameters']
        ) {
            return new ParameterBag();
        }

        if (false === is_array($fixturesParameters)) {
            throw TypeErrorFactory::createForInvalidFixtureBagParameters($fixturesParameters);
        }

        return new ParameterBag($fixturesParameters);
    }
}
