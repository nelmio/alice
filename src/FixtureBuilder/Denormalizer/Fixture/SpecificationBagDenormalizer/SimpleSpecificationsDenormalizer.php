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

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

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
    )
    {
        $this->constructorDenormalizer = $constructorDenormalizer;
        $this->propertyDenormalizer = $propertyDenormalizer;
        $this->callsDenormalizer = $callsDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedSpecs): SpecificationBag
    {
        $constructor = null;
        $properties = new PropertyBag();
        $calls = new MethodCallBag();

        foreach ($unparsedSpecs as $unparsedPropertyName => $value) {
            if ('__construct' === $unparsedPropertyName) {
                $constructor = $this->denormalizeConstructor($value, $scope, $parser);

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
    ): MethodCallInterface
    {
        return (false === $value)
            ? new NoMethodCall()
            : $this->constructorDenormalizer->denormalize($scope, $parser, $value)
        ;
    }

    private function denormalizeProperty(
        PropertyDenormalizerInterface $propertyDenormalizer,
        FlagParserInterface $flagParser,
        string $unparsedPropertyName,
        $value,
        PropertyBag $properties,
        FixtureInterface $scope
    ): PropertyBag
    {
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
    ): MethodCallBag
    {
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
    ): MethodCallInterface
    {
        if (false === is_array($methodCall)) {
            throw new \TypeError(
                sprintf(
                    'Expected method call value to be an array, got "%s" instead.',
                    gettype($methodCall)
                )
            );
        }
        $unparsedMethod = key($methodCall);
        if (false === is_string($unparsedMethod)) {
            throw new \TypeError(
                sprintf(
                    'Expected method name, got "%s" instead.',
                    gettype($unparsedMethod)
                )
            );
        }

        return $callsDenormalizer->denormalize(
            $scope,
            $parser,
            $unparsedMethod,
            $methodCall[$unparsedMethod]
        );
    }
}
