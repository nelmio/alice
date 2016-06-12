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

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

class CallsDenormalizer
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
     * Denormalizes a method call.
     *
     * @param FixtureInterface    $scope See SpecificationsDenormalizerInterface
     * @param FlagParserInterface $parser
     * @param string              $unparsedMethod
     * @param array               $unparsedArguments
     *
     * @return MethodCallInterface|null
     *
     * @example
     *  $unparsedMethod = 'setLocation (50%?)'
     */
    public final function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        string $unparsedMethod,
        array $unparsedArguments
    ): MethodCallInterface
    {
        $methodFlags = $parser->parse($unparsedMethod);
        $method = $methodFlags->getKey();
        $arguments = $this->argumentDenormalizer->denormalize($scope, $parser, $unparsedArguments);

        $methodCall = new SimpleMethodCall($method, $arguments);

        return $this->handleMethodFlags($scope, $methodCall, $methodFlags);
    }

    protected function handleMethodFlags(
        FixtureInterface $scope,
        MethodCallInterface $methodCall,
        FlagBag $flags
    ): MethodCallInterface
    {
        foreach ($flags as $flag) {
            if ($flag instanceof OptionalFlag) {
                return new OptionalMethodCall($methodCall, $flag);
            }
        }

        return $methodCall;
    }
}
