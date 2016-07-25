<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

class SimpleSpecificationsDenormalizer implements SpecificationsDenormalizerInterface
{
    /**
     * @var PropertyDenormalizer
     */
    private $propertyDenormalizer;

    /**
     * @var ConstructorDenormalizer
     */
    private $constructorDenormalizer;

    /**
     * @var CallsDenormalizer
     */
    private $callsDenormalizer;

    public function __construct()
    {
        $this->constructorDenormalizer = new ConstructorDenormalizer();
        $this->propertyDenormalizer = new PropertyDenormalizer();
        $this->callsDenormalizer = new CallsDenormalizer();
    }

    /**
     * {@inheritdoc}
     *
     * @param FixtureInterface    $scope
     * @param FlagParserInterface $parser
     * @param array               $unparsedSpecs
     *
     * @return SpecificationBag
     *
     * @example
     *  $unrparsedSpecs = [
     *      '__construct' => [
     *          'create' => [
     *              '<name()>',
     *          ]
     *      ],
     *      'username' => 'bob',
     *  ]
     */
    public final function denormalizer(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedSpecs): SpecificationBag
    {
        $constructor = null;
        $properties = new PropertyBag();
        $calls = new MethodCallBag();

        foreach ($unparsedSpecs as $unparsedPropertyName => $value) {
            if ('__construct' === $unparsedPropertyName) {
                $constructor = (false === $value)
                    ? new NoMethodCall()
                    : $this->denormalizeConstructor($scope, $parser, $value)
                ;

                continue;
            }

            if ('__calls' === $unparsedPropertyName) {
                foreach ($value as $methodCall) {
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

                    $calls = $calls->with(
                        $this->denormalizeCall($scope, $parser, $unparsedMethod, $methodCall[$unparsedMethod])
                    );
                }

                continue;
            }

            $flags = $parser->parse($unparsedPropertyName);
            $propertyName = $flags->getKey();

            $properties = $properties->with(
                $this->denormalizeProperty($scope, $propertyName, $value, $flags)
            );
        }

        return new SpecificationBag($constructor, $properties, $calls);
    }

    protected function denormalizeConstructor(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedConstructor): MethodCallInterface
    {
        return $this->constructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
    }

    protected function denormalizeCall(FixtureInterface $scope, FlagParserInterface $parser, string $unparsedMethod, array $unparsedArguments): MethodCallInterface
    {
        return $this->callsDenormalizer->denormalize($scope, $parser, $unparsedMethod, $unparsedArguments);
    }

    protected function denormalizeProperty(FixtureInterface $scope, string $name, $value, FlagBag $flags): Property
    {
        return $this->propertyDenormalizer->denormalize($scope, $name, $value, $flags);
    }
}
