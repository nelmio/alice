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
        $this->processor       = $processor;
        $this->typeHintChecker = $typeHintChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function canInstantiate(Fixture $fixture)
    {
        $refl = new \ReflectionMethod($fixture->getClass(), $fixture->getConstructorMethod());

        return $fixture->shouldUseConstructor() && $refl->getNumberOfRequiredParameters() <= count($fixture->getConstructorArgs());
    }

    /**
     * {@inheritDoc}
     */
    public function instantiate(Fixture $fixture)
    {
        $class             = $fixture->getClass();
        $constructorMethod = $fixture->getConstructorMethod();
        $constructorArgs   = $fixture->getConstructorArgs();

        $reflClass = new \ReflectionClass($class);

        $constructorArgs = $this->processor->process($constructorArgs, [], $fixture->getValueForCurrent());

        foreach ($constructorArgs as $index => $value) {
            $constructorArgs[$index] = $this->typeHintChecker->check($class, $constructorMethod, $value, $index);
        }

        if ($constructorMethod === '__construct') {
            $instance = $reflClass->newInstanceArgs($constructorArgs);
        } else {
            $instance = forward_static_call_array([$class, $constructorMethod], $constructorArgs);
            if (!($instance instanceof $class)) {
                throw new \UnexpectedValueException("The static constructor '{$constructorMethod}' for object '{$fixture}' returned an object that is not an instance of '{$class}'");
            }
        }

        return $instance;
    }
}
