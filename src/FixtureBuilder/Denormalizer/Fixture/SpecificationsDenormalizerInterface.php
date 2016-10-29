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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\DenormalizationThrowable;

interface SpecificationsDenormalizerInterface
{
    /**
     * @param FixtureInterface $fixture Fixture to which the specifications will be "attached to". Indeed some values
     *                                  may be bound to a scope (e.g. unique values). To guarantee the absolute
     *                                  uniqueness of the values, a good thing is to make them relative to their
     *                                  fixtures. So in practice we often pass the fixture being instantiated and will
     *                                  assign it the specifications bags to it later.
     * @param FlagParserInterface $parser
     * @param array $unparsedSpecs
     *
     * @throws DenormalizationThrowable
     *
     * @return SpecificationBag
     *
     * @example
     *  $unrparsedSpecs = [
     *      '__construct' => [
     *          'create' => [
     *              '<name()>',
     *          ]
     *      ],
     *      'username' => 'bob',
     *  ]
     */
    public function denormalize(FixtureInterface $fixture, FlagParserInterface $parser, array $unparsedSpecs): SpecificationBag;
}
