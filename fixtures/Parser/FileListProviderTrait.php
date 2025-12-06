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
    public static function providePhpList(): array
    {
        return FilesReference::getPhpList();
    }

    public static function provideYamlList(): array
    {
        return FilesReference::getYamlList();
    }

    public static function provideJsonList(): array
    {
        return FilesReference::getJsonList();
    }

    public static function provideUnsupportedList(): array
    {
        return FilesReference::getUnsupportedList();
    }
}
