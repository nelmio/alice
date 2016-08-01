<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator\Property;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Exception\Generator\Hydrator\HydratorNotFoundException;
use Nelmio\Alice\Exception\Generator\Hydrator\NoSuchPropertyException;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;

final class PropertyHydratorRegistry implements PropertyHydratorInterface
{
    use NotClonableTrait;

    /**
     * @var PropertyHydratorInterface[]
     */
    private $hydrators;

    /**
     * @param PropertyHydratorInterface[] $hydrators
     */
    public function __construct(array $hydrators)
    {
        $this->hydrators = (function (PropertyHydratorInterface ...$hydrators) { return $hydrators; })(...$hydrators);
    }

    /**
     * Tries each decorated hydrator to hydrate the given object. If hydration fails, try with the next hydrator until
     * either the object is hydrated or no hydrator is left.
     *
     * {@inheritdoc}
     */
    public function hydrate(ObjectInterface $object, Property $property): ObjectInterface
    {
        $lastError = null;
        foreach ($this->hydrators as $hydrator) {
            try {
                return $hydrator->hydrate($object, $property);
            } catch (NoSuchPropertyException $throwable) {
                $lastError = $throwable;
                // Continue
            }
        }

        if (null === $lastError) {
            throw HydratorNotFoundException::create($object, $property);
        }

        throw $lastError;
    }
}
