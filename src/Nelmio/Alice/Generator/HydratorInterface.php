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
use Nelmio\Alice\ObjectInterface;

interface HydratorInterface
{
    /**
     * Hydrate the object with the provided.
     *
     * @param ObjectInterface $object
     * @param Property        $property
     *
     * @return ObjectInterface
     */
    public function hydrate(ObjectInterface $object, Property $property): ObjectInterface;
}
