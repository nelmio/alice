<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments\SimpleArgumentsDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

final class OptionalCallsDenormalizer implements CallsDenormalizerInterface
{
    use NotClonableTrait;

    /**
     * @var ArgumentsDenormalizerInterface
     */
    private $argumentDenormalizer;

    public function __construct(ArgumentsDenormalizerInterface $argumentsDenormalizer)
    {
        $this->argumentDenormalizer = $argumentsDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
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

        return $this->handleMethodFlags($methodCall, $methodFlags);
    }

    private function handleMethodFlags(MethodCallInterface $methodCall, FlagBag $flags): MethodCallInterface
    {
        foreach ($flags as $flag) {
            if ($flag instanceof OptionalFlag) {
                return new OptionalMethodCall($methodCall, $flag);
            }
        }

        return $methodCall;
    }
}
