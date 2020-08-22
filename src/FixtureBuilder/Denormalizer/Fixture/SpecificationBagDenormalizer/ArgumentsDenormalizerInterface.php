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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * Denormalizer for a function call arguments.
 */
interface ArgumentsDenormalizerInterface
{
    /**
     * Denormalizes an array of arguments.
     *
     *
     * @throws DenormalizationThrowable
     *
     * @return array|ValueInterface[]
     *
     * @example
     *  example1:
     *  $unparsedArguments = [
     *      '<latitude()>',
     *      '<longitude()>',
     *  ],
     *
     *  example2:
     *  $unparsedArguments = [
     *      '0 (unique) => '<latitude()>',
     *      1 => '<longitude()>',
     *  ],
     */
    public function denormalize(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedArguments): array;
}
