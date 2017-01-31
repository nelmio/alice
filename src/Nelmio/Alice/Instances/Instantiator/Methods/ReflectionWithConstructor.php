<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

class ReflectionWithConstructor implements MethodInterface
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var TypeHintChecker
     */
    protected $typeHintChecker;

    public function __construct(Processor $processor, TypeHintChecker $typeHintChecker)
    {
        $this->processor = $processor;
        $this->typeHintChecker = $typeHintChecker;
    }

    /**
     * {@inheritDoc}
     *
     * Can instantiate only if the constructor method should be used and enough arguments for it are being passed.
     */
    public function canInstantiate(Fixture $fixture)
    {
        $refl = new \ReflectionMethod($fixture->getClass(), $fixture->getConstructorMethod());

        return (
            $fixture->shouldUseConstructor()
            && $refl->getNumberOfRequiredParameters() <= count($fixture->getConstructorArgs())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        $class = $fixture->getClass();
        $constructorMethod = $fixture->getConstructorMethod();
        $constructorArgs = $this->resolveConstructorArguments($fixture);

        try {
            $constructorRefl = (new \ReflectionClass($class))->getMethod($constructorMethod);
            if (false === $constructorRefl->isPublic()) {
                @trigger_error(
                    'Using a private or protected constructor is deprecated since 2.3.0 and will be removed in '
                    .'3.0.0.',
                    E_USER_DEPRECATED
                );
            }
        } catch (\ReflectionException $e) {
            // Continue
        }

        if ($constructorMethod === '__construct') {
            $reflectionClass = new \ReflectionClass($class);

            return $reflectionClass->newInstanceArgs($constructorArgs);
        }

        $instance = forward_static_call_array([$class, $constructorMethod], $constructorArgs);
        if (!($instance instanceof $class)) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Expected the static constructor "%s" for the fixture "%s" to return an instance of "%s". Got "%s" '
                    .'instead',
                    $constructorMethod,
                    $fixture->getName(),
                    $class,
                    is_object($instance) ? get_class($instance) : $instance
                )
            );
        }

        return $instance;
    }

    /**
     * @param Fixture $fixture
     *
     * @return array Resolved constructor arguments
     */
    private function resolveConstructorArguments(Fixture $fixture)
    {
        $constructorArguments = $this->processor->process(
            $fixture->getConstructorArgs(),
            [],
            $fixture->getValueForCurrent()
        );

        foreach ($constructorArguments as $index => $value) {
            $constructorArguments[$index] = $this->typeHintChecker->check(
                $fixture->getClass(),
                $fixture->getConstructorMethod(),
                $value,
                $index
            );
        }

        return $constructorArguments;
    }
}
