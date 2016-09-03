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

use Nelmio\Alice\Bridge\Symfony\Application\AppKernel;
use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\Loader\IsolatedSymfonyLoader;
use Nelmio\Alice\Loader\AbstractLoaderIntegrationTestCase;

/**
 * {@inheritdoc}
 *
 * @group symfony
 */
class LoaderIntegrationTest extends AbstractLoaderIntegrationTestCase
{
    /**
     * @return FileLoaderInterface|DataLoaderInterface
     */
    public function getLoader()
    {
        return new IsolatedSymfonyLoader(AppKernel::class);
    }
}
