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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\IsAServiceTrait;

final class CallsWithFlagsDenormalizer implements CallsDenormalizerInterface
{
    use IsAServiceTrait;

    /**
     * @var CallsDenormalizerInterface
     */
    private $callsDenormalizer;

    /**
     * @var MethodFlagHandler[]
     */
    private $methodFlagHandlers;

    /**
     * @param MethodFlagHandler[]        $methodFlagHandlers
     */
    public function __construct(CallsDenormalizerInterface $callsDenormalizer, array $methodFlagHandlers)
    {
        $this->callsDenormalizer = $callsDenormalizer;
        $this->methodFlagHandlers = (static function (MethodFlagHandler ...$handlers) {
            return $handlers;
        })(...$methodFlagHandlers);
    }
    
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        string $unparsedMethod,
        array $unparsedArguments
    ): MethodCallInterface {
        $methodFlags = $parser->parse($unparsedMethod);
        $method = $methodFlags->getKey();

        $methodCall = $this->callsDenormalizer->denormalize($scope, $parser, $method, $unparsedArguments);

        return $this->handleMethodFlags($methodCall, $methodFlags);
    }

    private function handleMethodFlags(MethodCallInterface $methodCall, FlagBag $flags): MethodCallInterface
    {
        foreach ($this->methodFlagHandlers as $methodFlagHandler) {
            foreach ($flags as $flag) {
                $methodCall = $methodFlagHandler->handleMethodFlags($methodCall, $flag);
            }
        }

        return $methodCall;
    }
}
