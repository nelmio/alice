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

namespace Nelmio\Alice\Parser;

trait FileListProviderTrait
{
    public function providePhpList()
    {
        return FilesReference::getPhpList();
    }

    public function provideYamlList()
    {
        return FilesReference::getYamlList();
    }

    public function provideJsonList()
    {
        return FilesReference::getJsonList();
    }

    public function provideUnsupportedList()
    {
        return FilesReference::getUnsupportedList();
    }
}
