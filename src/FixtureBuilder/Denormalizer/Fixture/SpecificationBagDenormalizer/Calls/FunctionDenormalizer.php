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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class FunctionDenormalizer implements CallsDenormalizerInterface
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
    
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        string $unparsedMethod,
        array $unparsedArguments
    ): MethodCallInterface {
        [$caller, $method] = $this->getCallerReference($scope, $unparsedMethod);
        $arguments = $this->argumentsDenormalizer->denormalize($scope, $parser, $unparsedArguments);

        if (null === $caller) {
            return new SimpleMethodCall($method, $arguments);
        }

        return new MethodCallWithReference($caller, $method, $arguments);
    }

    /**
     * @return array The first element is a ServiceReferenceInterface ($caller) and the second a string ($method)
     */
    private function getCallerReference(FixtureInterface $scope, string $method): array
    {
        if (false === strpos($method, '::')) {
            return [null, $method];
        }

        $explodedMethod = explode('::', $method);
        if (2 < count($explodedMethod)) {
            throw InvalidArgumentExceptionFactory::createForInvalidConstructorMethod($method);
        }

        [$caller, $method] = $explodedMethod;

        if (0 === strpos($caller, '@')) {
            return [new InstantiatedReference(substr($caller, 1)), $method];
        }

        return [new StaticReference($caller), $method];
    }
}
