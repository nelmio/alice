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
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

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
     * @param array $unparsedSpecs
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
     *
     * @throws DenormalizationThrowable
     *
     * @return SpecificationBag
     */
    public final function denormalizer(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedSpecs): SpecificationBag
    {
        $constructor = null;
        $properties = new PropertyBag();
        $calls = new MethodCallBag();

        foreach ($unparsedSpecs as $unparsedPropertyName => $value) {
            $flags = $parser->parse($unparsedPropertyName);
            $propertyName = $flags->getKey();

            if ('__construct' === $propertyName && null !== $propertyName) {
                $constructor = $this->denormalizeConstructor($scope, $parser, $value, $flags);

                continue;
            }

            if ('__calls' === $propertyName) {
                foreach ($value as $unparsedMethod => $unparsedArguments) {
                    $calls = $this->denormalizeCall($scope, $parser, $unparsedMethod, $unparsedArguments, $flags);
                }

                continue;
            }

            $properties = $properties->with(
                $this->denormalizeProperty($scope, $propertyName, $value, $flags)
            );
        }

        return new SpecificationBag($constructor, $properties, $calls);
    }

    protected function denormalizeConstructor(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedConstructor, FlagBag $flags): MethodCallInterface
    {
        $this->constructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor, $flags);
    }

    protected function denormalizeCall(FixtureInterface $scope, FlagParserInterface $parser, string $unparsedMethod, string $unparsedArguments, FlagBag $flags): MethodCallBag
    {
        $this->callsDenormalizer->denormalize($scope, $parser, $unparsedMethod, $unparsedArguments);
    }

    protected function denormalizeProperty(FixtureInterface $scope, string $name, $value, FlagBag $flags): Property
    {
        $this->propertyDenormalizer->denormalize($scope, $name, $value, $flags);
    }
}
