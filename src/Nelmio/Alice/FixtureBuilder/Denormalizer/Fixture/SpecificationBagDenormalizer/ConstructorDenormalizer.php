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
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

final class ConstructorDenormalizer
{
    /**
     * @var ArgumentsDenormalizer
     */
    private $argumentDenormalizer;

    public function __construct()
    {
        $this->argumentDenormalizer = new ArgumentsDenormalizer();
    }
    
    /**
     * @TODO
     * 
     * @param FixtureInterface    $scope
     * @param FlagParserInterface $parser
     * @param                     $unparsedConstructor
     * @param FlagBag                    $flags
     *
     * @return MethodCallInterface|null
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedConstructor,
        FlagBag $flags
    ): MethodCallInterface
    {
        $firstKey = key($unparsedConstructor);
        if (is_int($firstKey) || count($unparsedConstructor) > 1) {
            return new SimpleMethodCall(
                '__construct',
                $this->argumentDenormalizer->denormalize($scope, $parser, $unparsedConstructor)
            );
        }

        if (false !== strpos($firstKey, '(')) {
            return new SimpleMethodCall(
                '__construct',
                $this->argumentDenormalizer->denormalize($scope, $parser, $unparsedConstructor)
            );
        }

        list($caller, $method) = $this->getCallerReference($scope, $firstKey);
        $arguments = $this->argumentDenormalizer->denormalize($scope, $parser, $unparsedConstructor[$firstKey]);

        return new MethodCallWithReference($caller, $method, $arguments);
    }

    /**
     * @param FixtureInterface $scope
     * @param string           $method
     *
     * @return array<ServiceReferenceInterface $caller, string $method>
     */
    private function getCallerReference(FixtureInterface $scope, string $method): array 
    {
        if (false !== strpos($method, '::')) {
            return [new StaticReference($scope->getClassName()), $method];
        }
        
        $explodedMethod = explode('::', $method);
        if (2 > count($explodedMethod)) {
            throw new \InvalidArgumentException('TODO');
        }

        list($caller, $method) = $explodedMethod;

        if (0 === strpos($caller, '@')) {
            return [new InstantiatedReference(substr($method, 1)), $method];
        }
        
        return [new StaticReference($caller), $method];
    }
}
