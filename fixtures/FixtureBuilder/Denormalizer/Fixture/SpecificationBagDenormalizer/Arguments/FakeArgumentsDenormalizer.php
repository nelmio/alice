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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Arguments;

use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ArgumentsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeArgumentsDenormalizer implements ArgumentsDenormalizerInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function denormalize(FixtureInterface $scope, FlagParserInterface $parser, array $unparsedArguments): array
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
