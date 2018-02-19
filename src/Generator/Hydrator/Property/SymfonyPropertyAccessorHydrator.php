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

namespace Nelmio\Alice\Generator\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InaccessiblePropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InvalidArgumentException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\AccessException as SymfonyAccessException;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface as SymfonyPropertyAccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use TypeError;

final class SymfonyPropertyAccessorHydrator implements PropertyHydratorInterface
{
    use IsAServiceTrait;

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
     * @throws InaccessiblePropertyException
     * @throws InvalidArgumentException When the typehint does not match for example
     * @throws HydrationException
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $instance = $object->getInstance();
        try {
            $this->propertyAccessor->setValue($instance, $property->getName(), $property->getValue());
        } catch (SymfonyNoSuchPropertyException $exception) {
            throw HydrationExceptionFactory::createForCouldNotHydrateObjectWithProperty($object, $property, 0, $exception);
        } catch (SymfonyAccessException $exception) {
            throw HydrationExceptionFactory::createForInaccessibleProperty($object, $property, 0, $exception);
        } catch (SymfonyInvalidArgumentException $exception) {
            throw HydrationExceptionFactory::createForInvalidProperty($object, $property, 0, $exception);
        } catch (SymfonyPropertyAccessException $exception) {
            throw HydrationExceptionFactory::create($object, $property, 0, $exception);
        } catch (TypeError $error) {
            throw HydrationExceptionFactory::createForInvalidProperty($object, $property, 0, $error);
        }

        return new SimpleObject($object->getId(), $instance);
    }
}
