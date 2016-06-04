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
use Nelmio\Alice\ParameterBag;

final class SimpleParameterBagBuilder implements ParameterBagDenormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param array $data Full set of parsed data, will look for the parameter subset itself.
     *
     * @return ParameterBag
     */
    public function denormalize(array $data): ParameterBag
    {
        if (false === array_key_exists('parameters', $data)) {
            return new ParameterBag();
        }

        $parameters = $data['parameters'];
        if (false === is_array($parameters)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected parameters to be an array. Got "%s" instead.',
                    is_object($parameters) ? get_class($parameters) : gettype($parameters)
                )
            );
        }

        return new ParameterBag($parameters);
    }
}
