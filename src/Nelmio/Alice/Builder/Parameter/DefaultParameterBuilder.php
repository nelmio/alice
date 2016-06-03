<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Parameter;

use Nelmio\Alice\Builder\ParameterBagBuilderInterface;
use Nelmio\Alice\ParameterBag;

final class DefaultParameterBuilder implements ParameterBagBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $fixtures): ParameterBag
    {
        if (false === array_key_exists('parameters', $fixtures)) {
            return new ParameterBag();
        }

        $fixturesParameters = $fixtures['parameters'];
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
