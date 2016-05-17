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

use Nelmio\Alice\Fixture\ParameterBag;

/**
 * @internal
 * @final
 */
class ParameterBuilder
{
    /**
     * Builds the parameter bag from the passed data.
     *
     * @param array $data
     *
     * @throws \TypeError
     *
     * @return ParameterBag
     */
    public function build(array $data): ParameterBag
    {
        $parameterBag = new ParameterBag();

        if (false === array_key_exists('parameters', $data)) {
            return $parameterBag;
        }
        $parameters = $data['parameters'];

        if (false === is_array($parameters)) {
            throw new \TypeError(
                sprintf(
                    'Parameters block must be an array. Found "%s" instead.',
                    gettype($parameters)
                )
            );
        }

        return $parameterBag->with($parameters);
    }
}
