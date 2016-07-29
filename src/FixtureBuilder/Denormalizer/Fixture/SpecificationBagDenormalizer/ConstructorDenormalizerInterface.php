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

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface ConstructorDenormalizerInterface
{
    /**
     * Denormalizes a constructor.
     *
     * @param FixtureInterface    $scope
     * @param FlagParserInterface $parser
     * @param array               $unparsedConstructor
     *
     * @throws DenormalizationThrowable
     *
     * @return MethodCallInterface
     *
     * @example
     *  example1:
     *  $unparsedConstructor = [
     *      '<latitude()>',
     *      '<longitude()>',
     *  ],
     *
     *  example2:
     *  $unparsedConstructor = [
     *      create => [
     *          '0 (unique) => '<latitude()>',
     *          1 => '<longitude()>',
     *      ]
     *  ],
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        array $unparsedConstructor
    ): MethodCallInterface;
}
