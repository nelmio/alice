<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Bridge\Symfony\Loader;

use Nelmio\Alice\Loader\IsolatedSymfonyLoader;

/**
 * @group integration
 * @group symfony
 */
class LoaderIntegrationTest extends \Nelmio\Alice\Loader\LoaderIntegrationTest
{
    public function setUp()
    {
        $this->loader = new IsolatedSymfonyLoader();
    }
}
