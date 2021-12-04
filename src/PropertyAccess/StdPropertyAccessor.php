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

namespace Nelmio\Alice\PropertyAccess;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\PropertyAccess\NoSuchPropertyExceptionFactory;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class StdPropertyAccessor implements PropertyAccessorInterface
{
    use IsAServiceTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $decoratedPropertyAccessor;

    public function __construct(PropertyAccessorInterface $decoratedPropertyAccessor)
    {
        $this->decoratedPropertyAccessor = $decoratedPropertyAccessor;
    }
    
    public function setValue(&$objectOrArray, $propertyPath, $value): void
    {
        if ($objectOrArray instanceof stdClass) {
            $objectOrArray->{$propertyPath} = $value;

            return;
        }

        $this->decoratedPropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
    }
    
    public function getValue($objectOrArray, $propertyPath): mixed
    {
        if (false === $objectOrArray instanceof stdClass) {
            return $this->decoratedPropertyAccessor->getValue($objectOrArray, $propertyPath);
        }

        if (false === isset($objectOrArray->$propertyPath)) {
            throw NoSuchPropertyExceptionFactory::createForUnreadablePropertyFromStdClass($propertyPath);
        }

        return $objectOrArray->$propertyPath;
    }
    
    public function isWritable($objectOrArray, $propertyPath): bool
    {
        return ($objectOrArray instanceof stdClass)
            ? true
            : $this->decoratedPropertyAccessor->isWritable($objectOrArray, $propertyPath)
        ;
    }
    
    public function isReadable($objectOrArray, $propertyPath): bool
    {
        return ($objectOrArray instanceof stdClass)
            ? isset($objectOrArray->$propertyPath)
            : $this->decoratedPropertyAccessor->isReadable($objectOrArray, $propertyPath)
        ;
    }
}
