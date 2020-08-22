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

namespace Nelmio\Alice\Bridge\Symfony\Application;

use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernelWithCustomParameters extends AppKernel
{
    /**
     * @inheritdoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (3 === self::MAJOR_VERSION) {
            $config = __DIR__.'/config_custom_34.yml';
        } else {
            $config = __DIR__.'/config_custom.yml';
        }

        $loader->load($config);
    }
}
