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

namespace Nelmio\Alice\Bridge\Symfony\Generator\Resolver;

use Nelmio\Alice\Generator\Resolver\Parameter\IsolatedSymfonyParameterBagResolver;

/**
 * @group integration
 * @coversNothing
 */
class ParameterResolverIntegrationTest extends \Nelmio\Alice\Generator\Resolver\ParameterResolverIntegrationTest
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->resolver = new IsolatedSymfonyParameterBagResolver();
    }
}
