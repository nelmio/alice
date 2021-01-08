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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use InvalidArgumentException;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

final class SimpleSpecificationsDenormalizer implements SpecificationsDenormalizerInterface
{
    /**
     * @var ConstructorDenormalizerInterface
     */
    private $constructorDenormalizer;

    /**
     * @var PropertyDenormalizerInterface
     */
    private $propertyDenormalizer;

    /**
     * @var CallsDenormalizerInterface
     */
    private $callsDenormalizer;

    public function __construct(
        ConstructorDenormalizerInterface $constructorDenormalizer,
        PropertyDenormalizerInterface $propertyDenormalizer,
        CallsDenormalizerInterface $callsDenormalizer
    ) {
        $this->constructorDenormalizer = $constructorDenormalizer;
        $this->propertyDenormalizer = $propertyDenormalizer;
        $this->callsDenormalizer = $callsDenormalizer;
    }
    
    public function denormalize(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedSpecs): SpecificationBag
    {
        $constructor = null;
        $properties = new PropertyBag();
        $calls = new MethodCallBag();

        foreach ($unparsedSpecs as $unparsedPropertyName => $value) {
            if (false === is_string($unparsedPropertyName)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid property name: %s.',
                        $unparsedPropertyName
                    )
                );
            }

            if ('__construct' === $unparsedPropertyName) {
                $constructor = $this->denormalizeConstructor($value, $scope, $parser);

                if (false === ($constructor instanceof NoMethodCall) && '__construct' !== $constructor->getMethod()) {
                    @trigger_error(
                        'Using factories with the fixture keyword "__construct" has been deprecated since '
                        .'3.0.0 and will no longer be supported in Alice 4.0.0. Use "__factory" instead.',
                        E_USER_DEPRECATED
                    );
                }

                continue;
            }

            if ('__factory' === $unparsedPropertyName) {
                if (null !== $constructor) {
                    throw LogicExceptionFactory::createForCannotHaveBothConstructorAndFactory();
                }

                $constructor = $this->denormalizeFactory($value, $scope, $parser);

                continue;
            }

            if ('__calls' === $unparsedPropertyName) {
                $calls = $this->denormalizeCall($this->callsDenormalizer, $value, $calls, $scope, $parser);

                continue;
            }

            $properties = $this->denormalizeProperty($this->propertyDenormalizer, $parser, $unparsedPropertyName, $value, $properties, $scope);
        }

        return new SpecificationBag($constructor, $properties, $calls);
    }

    private function denormalizeConstructor(
        $value,
        FixtureInterface $scope,
        FlagParserInterface $parser
    ): MethodCallInterface {
        return (false === $value)
            ? new NoMethodCall()
            : $this->constructorDenormalizer->denormalize($scope, $parser, $value)
        ;
    }

    private function denormalizeFactory(
        $value,
        FixtureInterface $scope,
        FlagParserInterface $parser
    ): MethodCallInterface {
        $factory = $this->denormalizeConstructor($value, $scope, $parser);

        if ('__construct' === $factory->getMethod()) {
            throw DenormalizerExceptionFactory::createForUndenormalizableFactory();
        }

        return $factory;
    }

    private function denormalizeProperty(
        PropertyDenormalizerInterface $propertyDenormalizer,
        FlagParserInterface $flagParser,
        string $unparsedPropertyName,
        $value,
        PropertyBag $properties,
        FixtureInterface $scope
    ): PropertyBag {
        $flags = $flagParser->parse($unparsedPropertyName);
        $propertyName = $flags->getKey();

        $property = $propertyDenormalizer->denormalize($scope, $propertyName, $value, $flags);

        return $properties->with($property);
    }

    private function denormalizeCall(
        CallsDenormalizerInterface $callsDenormalizer,
        $value,
        MethodCallBag $calls,
        FixtureInterface $scope,
        FlagParserInterface $parser
    ): MethodCallBag {
        foreach ($value as $methodCall) {
            $methodCall = $this->denormalizeCallMethod($callsDenormalizer, $methodCall, $scope, $parser);
            $calls = $calls->with($methodCall);
        }

        return $calls;
    }

    private function denormalizeCallMethod(
        CallsDenormalizerInterface $callsDenormalizer,
        $methodCall,
        FixtureInterface $scope,
        FlagParserInterface $parser
    ): MethodCallInterface {
        if (false === is_array($methodCall)) {
            throw TypeErrorFactory::createForInvalidSpecificationBagMethodCall($methodCall);
        }

        $unparsedMethod = key($methodCall);
        if (false === is_string($unparsedMethod)) {
            throw TypeErrorFactory::createForInvalidSpecificationBagMethodCallName($unparsedMethod);
        }

        return $callsDenormalizer->denormalize(
            $scope,
            $parser,
            $unparsedMethod,
            $methodCall[$unparsedMethod]
        );
    }
}
