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

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Exception\Generator\Hydrator\NoSuchPropertyException;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;
use Symfony\Component\PropertyAccess\Exception\AccessException as SymfonyAccessException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class SymfonyPropertyAccessorHydrator implements PropertyHydratorInterface
{
    use NotClonableTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoSuchPropertyException
     * @throws HydrationException
     */
    public function hydrate(ObjectInterface $object, Property $property): ObjectInterface
    {
        $instance = $object->getInstance();
        try {
            if ($instance instanceof \stdClass) {
                $instance->{$property->getName()} = $property->getValue();
            } else {
                $this->propertyAccessor->setValue($instance, $property->getName(), $property->getValue());
            }
        } catch (SymfonyNoSuchPropertyException $exception) {
            throw NoSuchPropertyException::create($object, $property, 0, $exception);
        } catch (SymfonyAccessException $exception) {
            throw HydrationException::create($object, $property, 0, $exception);
        }

        return new SimpleObject($object->getReference(), $instance);
    }
}
