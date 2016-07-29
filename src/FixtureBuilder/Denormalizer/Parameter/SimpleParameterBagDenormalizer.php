<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter;

use Nelmio\Alice\FixtureBuilder\Denormalizer\ParameterBagDenormalizerInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ParameterBag;

final class SimpleParameterBagDenormalizer implements ParameterBagDenormalizerInterface
{
    use NotClonableTrait;

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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected parameters to be an array. Got "%s" instead.',
                    is_object($fixturesParameters) ? get_class($fixturesParameters) : gettype($fixturesParameters)
                )
            );
        }

        return new ParameterBag($fixturesParameters);
    }
}
