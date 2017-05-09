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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;

final class FactoryDenormalizer implements ConstructorDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var ArgumentsDenormalizerInterface
     */
    private $argumentsDenormalizer;

    public function __construct(ArgumentsDenormalizerInterface $argumentsDenormalizer)
    {
        $this->argumentsDenormalizer = $argumentsDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedConstructor
    ): MethodCallInterface
    {
        /** @var string $firstKey */
        $firstKey = key($unparsedConstructor);

        if (false === $firstKey
            || false === is_string($firstKey)
            || 1 !== count($unparsedConstructor)
        ) {
            throw DenormalizerExceptionFactory::createForUndenormalizableFactory();
        }

        $arguments = $unparsedConstructor[$firstKey];

        if (false === is_array($arguments)) {
            throw DenormalizerExceptionFactory::createForUndenormalizableFactory();
        }

        list($caller, $method) = $this->getCallerReference($scope, $firstKey);
        $arguments = $this->argumentsDenormalizer->denormalize($scope, $parser, $unparsedConstructor[$firstKey]);

        return new MethodCallWithReference($caller, $method, $arguments);
    }

    /**
     * @param FixtureInterface $scope
     * @param string           $method
     *
     * @return array The first element is a ServiceReferenceInterface ($caller) and the second a string ($method)
     */
    private function getCallerReference(FixtureInterface $scope, string $method): array
    {
        if (false === strpos($method, '::')) {
            return [new StaticReference($scope->getClassName()), $method];
        }

        $explodedMethod = explode('::', $method);
        if (2 < count($explodedMethod)) {
            throw InvalidArgumentExceptionFactory::createForInvalidConstructorMethod($method);
        }

        list($caller, $method) = $explodedMethod;

        if (0 === strpos($caller, '@')) {
            return [new InstantiatedReference(substr($caller, 1)), $method];
        }

        return [new StaticReference($caller), $method];
    }
}
