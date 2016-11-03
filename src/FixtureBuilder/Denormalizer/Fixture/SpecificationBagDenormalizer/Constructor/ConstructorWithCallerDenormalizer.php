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
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnsupportedScenarioException;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

/**
 * Denormalizer handling factories.
 */
final class ConstructorWithCallerDenormalizer implements ConstructorDenormalizerInterface
{
    use NotClonableTrait;

    /**
     * @var SimpleConstructorDenormalizer
     */
    private $simpleConstructorDenormalizer;

    public function __construct(SimpleConstructorDenormalizer $simpleConstructorDenormalizer)
    {
        $this->simpleConstructorDenormalizer = $simpleConstructorDenormalizer;
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

        if (count($unparsedConstructor) !== 1
            || false === is_string($firstKey)
            || false === is_array($unparsedConstructor[$firstKey])
        ) {
            return $this->simpleConstructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
        }

        // Constructor can have 1 named array parameter or be a factory
        if ($this->isNamedParameter($scope, $firstKey)) {
            return $this->simpleConstructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
        }

        list($caller, $method) = $this->getCallerReference($scope, $firstKey);
        $arguments = $this->simpleConstructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor[$firstKey]);

        return new MethodCallWithReference($caller, $method, $arguments->getArguments());
    }

    private function isNamedParameter(FixtureInterface $fixture, string $parameterOrFunction): bool
    {
        try {
            $fixtureRefl = new \ReflectionClass($fixture->getClassName());
            $constructorRefl = $fixtureRefl->getMethod('__construct');
        } catch (\ReflectionException $exception) {
            return false;
        }

        if ($this->hasNamedParameter($constructorRefl, $parameterOrFunction)) {
            if ($this->hasStaticFactoryMethod($fixtureRefl, $parameterOrFunction)) {
                throw UnsupportedScenarioException::createForAmbiguousConstructor($fixture, $parameterOrFunction);
            }

            return true;
        }

        return false;
    }

    private function hasNamedParameter(\ReflectionMethod $method, string $parameterName): bool
    {
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameterName === $parameter->getName()) {
                return true;
            }
        }

        return false;
    }

    private function hasStaticFactoryMethod(\ReflectionClass $fixtureRefl, string $methodName): bool
    {
        try {
            $methodRefl = $fixtureRefl->getMethod($methodName);

            return $methodRefl->isStatic();
        } catch (\ReflectionException $exception) {
            return false;
        }
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid constructor or factory method "%s".',
                    $method
                )
            );
        }

        list($caller, $method) = $explodedMethod;

        if (0 === strpos($caller, '@')) {
            return [new InstantiatedReference(substr($caller, 1)), $method];
        }

        return [new StaticReference($caller), $method];
    }
}
