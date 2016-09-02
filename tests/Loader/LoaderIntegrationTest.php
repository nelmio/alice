<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;

/**
 * @group integration
 */
class LoaderIntegrationTest extends AbstractLoaderIntegrationTestCase
{
    /**
     * @return FileLoaderInterface|DataLoaderInterface
     */
    public function getLoader()
    {
        return new IsolatedLoader();
    }
}
