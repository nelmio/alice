<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Constructor;

use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

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
        try {
            return $this->simpleConstructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor);
        } catch (UnexpectedValueException $exception) {
            // Continue
        }

        /** @var string $firstKey */
        $firstKey = key($unparsedConstructor);
        list($caller, $method) = $this->getCallerReference($scope, $firstKey);
        $arguments = $this->simpleConstructorDenormalizer->denormalize($scope, $parser, $unparsedConstructor[$firstKey]);

        return new MethodCallWithReference($caller, $method, $arguments->getArguments());
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
                    'Invalid constructor method "%s".',
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
