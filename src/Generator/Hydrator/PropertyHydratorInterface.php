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

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\ObjectInterface;

interface PropertyHydratorInterface
{
    /**
     * Hydrate the object with the provided.
     *
     * @param ObjectInterface $object
     * @param Property        $property
     *
     * @return ObjectInterface
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface;
}
