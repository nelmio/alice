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

namespace Nelmio\Alice\Bridge\Symfony\Loader;

use Nelmio\Alice\Loader\LoaderIntegrationTest as CoreLoaderIntegrationTest;
use Nelmio\Alice\Loader\NonIsolatedSymfonyLoader;
use Nelmio\Alice\Symfony\KernelFactory;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @group integration
 * @coversNothing
 */
class LoaderIntegrationTest extends CoreLoaderIntegrationTest
{
    /**
     * @var KernelInterface
     */
    private static $kernel;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$kernel = KernelFactory::createKernel();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        static::$kernel->boot();

        $this->nonIsolatedLoader = $this->loader = new NonIsolatedSymfonyLoader(static::$kernel->getContainer());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        static::$kernel->shutdown();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        static::$kernel = null;

        parent::tearDownAfterClass();
    }
}
