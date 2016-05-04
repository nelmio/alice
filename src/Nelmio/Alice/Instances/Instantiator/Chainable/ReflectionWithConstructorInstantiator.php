<?php

/*
 * This file is part of the Alice package.
 *  
 *  (c) Nelmio <hello@nelm.io>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Chainable;

use Nelmio\Alice\Exception\Instantiator\UnexpectedValueException;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

final class ReflectionWithConstructorInstantiator implements ChainableInstantiatorInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var TypeHintChecker
     */
    private $typeHintChecker;

    public function __construct(Processor $processor, TypeHintChecker $typeHintChecker)
    {
        $this->processor = $processor;
        $this->typeHintChecker = $typeHintChecker;
    }

    /**
     * @inheritdoc
     */
    public function instantiate(Fixture $fixture)
    {
        $className = $fixture->getClass();
        $constructorMethod = $fixture->getConstructorMethod();

        $reflectionClass = new \ReflectionClass($className);
        $constructorArguments = $this->resolveConstructorArguments($fixture);

        if ($constructorMethod === '__construct') {
            return $reflectionClass->newInstanceArgs($constructorArguments);
        }

        $instance = forward_static_call_array([$className, $constructorMethod], $constructorArguments);
        if (false === $instance instanceof $className) {
            throw new UnexpectedValueException(
                sprintf(
                    'Expected the static constructor "%s" for the fixture "%s" to return an instance of "%s". Got "%s" '
                    .'instead',
                    $constructorMethod,
                    $fixture->getName(),
                    $className,
                    is_object($instance) ? get_class($instance) : $instance
                )
            );
        }

        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(Fixture $fixture): bool
    {
        try {
            $reflectionMethod = new \ReflectionMethod($fixture->getClass(), $fixture->getConstructorMethod());

            return (
                $fixture->shouldUseConstructor()
                && $reflectionMethod->getNumberOfRequiredParameters() <= count($fixture->getConstructorArgs())
            );
        } catch (\ReflectionException $exception) {
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function resolveConstructorArguments(Fixture $fixture)
    {
        $processedArguments = $this->processor->process(
            $fixture->getConstructorArgs(),
            [],
            $fixture->getValueForCurrent()
        );

        foreach ($processedArguments as $index => $value) {
            $processedArguments[$index] = $this->typeHintChecker->check(
                $fixture->getClass(),
                $fixture->getConstructorMethod(),
                $value,
                $index
            );
        }

        return $processedArguments;
    }
}
