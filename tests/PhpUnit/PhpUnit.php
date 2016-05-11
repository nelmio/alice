<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace PhpUnit;

final class PhpUnit extends \PHPUnit_Framework_TestCase
{
    public static function assertIsA(string $expected, string $actual)
    {
        $reflectionClass = new \ReflectionClass($actual);
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        \PHPUnit_Framework_TestCase::assertInstanceOf($expected, $instance);
    }

    public static function assertErrorMessageIs(string $expected, \Throwable $actual)
    {
        \PHPUnit_Framework_TestCase::assertEquals($expected, $actual->getMessage());
    }
}
