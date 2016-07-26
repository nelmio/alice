<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Definition\Property;

interface HydratorInterface
{
    /**
     * Hydrate the object with the provided.
     *
     * @param \object  $object
     * @param Property $property
     *
     * @return \object
     */
    public function hydrate($object, Property $property);
}
