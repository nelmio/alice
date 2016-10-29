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

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface CallsDenormalizerInterface
{
    /**
     * Denormalizes a method call.
     *
     * @param FixtureInterface    $scope See SpecificationsDenormalizerInterface
     * @param FlagParserInterface $parser
     * @param string              $unparsedMethod
     * @param array               $unparsedArguments
     *
     * @throws DenormalizationThrowable
     *
     * @return MethodCallInterface
     *
     * @example
     *  $unparsedMethod = 'setLocation (50%?)'
     */
    public function denormalize(
        FixtureInterface $scope,
        FlagParserInterface $parser,
        string $unparsedMethod,
        array $unparsedArguments
    ): MethodCallInterface;
}
