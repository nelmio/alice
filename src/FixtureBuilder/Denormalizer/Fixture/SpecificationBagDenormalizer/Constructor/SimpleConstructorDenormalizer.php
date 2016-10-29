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

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

final class SimpleConstructorDenormalizer implements ConstructorDenormalizerInterface
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
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedConstructor
    ): MethodCallInterface
    {
        /** @var int|string|null $firstKey */
        $firstKey = key($unparsedConstructor);
        if (null === $firstKey
            || is_int($firstKey)
            || count($unparsedConstructor) > 1
            || (is_string($firstKey) && preg_match('/\(.*\)/', $firstKey))
        ) {
            return new SimpleMethodCall(
                '__construct',
                $this->argumentDenormalizer->denormalize($scope, $parser, $unparsedConstructor)
            );
        }

        throw new UnexpectedValueException('Could not denormalize the given constructor.');
    }
}
