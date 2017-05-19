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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler;

use Nelmio\Alice\Definition\Flag\ConfiguratorFlag;
use Nelmio\Alice\Definition\FlagInterface;
use Nelmio\Alice\Definition\MethodCall\ConfiguratorMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\MethodFlagHandler;
use Nelmio\Alice\IsAServiceTrait;

final class ConfiguratorFlagHandler implements MethodFlagHandler
{
    use IsAServiceTrait;

    /**
     * @inheritdoc
     */
    public function handleMethodFlags(MethodCallInterface $methodCall, FlagInterface $flag): MethodCallInterface
    {
        return $flag instanceof ConfiguratorFlag
            ? new ConfiguratorMethodCall($methodCall)
            : $methodCall
        ;
    }
}
