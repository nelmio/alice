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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('integration')]
#[\PHPUnit\Framework\Attributes\CoversNothing]
final class LoaderIntegrationTest extends CoreLoaderIntegrationTest
{
    /**
     * @var KernelInterface
     */
    private static $kernel;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$kernel = KernelFactory::createKernel();
    }

    protected function setUp(): void
    {
        self::$kernel->boot();

        $this->nonIsolatedLoader = $this->loader = new NonIsolatedSymfonyLoader(self::$kernel->getContainer());
    }

    protected function tearDown(): void
    {
        self::$kernel->shutdown();
    }

    public static function tearDownAfterClass(): void
    {
        self::$kernel = null;

        parent::tearDownAfterClass();
    }
}
