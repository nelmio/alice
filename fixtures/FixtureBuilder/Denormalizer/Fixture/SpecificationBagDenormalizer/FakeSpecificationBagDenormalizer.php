<?php

/**
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;

class FakeSpecificationBagDenormalizer implements SpecificationsDenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function denormalizer(
        FixtureInterface $fixture,
        FlagParserInterface $parser,
        array $unparsedSpecs
    ): SpecificationBag
    {
        throw new \BadMethodCallException();
    }
}
