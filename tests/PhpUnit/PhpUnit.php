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
    /**
     * @param string $expected FQCN
     * @param string $actual   FQCN
     */
    public static function assertIsA($expected, $actual)
    {
        $reflectionClass = new \ReflectionClass($actual);
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        \PHPUnit_Framework_TestCase::assertInstanceOf($expected, $instance);
    }

    /**
     * @param string     $expected
     * @param \Exception $actual
     */
    public static function assertErrorMessageIs($expected, \Exception $actual)
    {
        \PHPUnit_Framework_TestCase::assertEquals($expected, $actual->getMessage());
    }
}
