<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FileLocator;

use Nelmio\Alice\FileLocatorInterface;

/**
 * @covers Nelmio\Alice\FileLocator\DefaultFileLocator
 */
class FileLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultFileLocator
     */
    private $locator;

    public function setUp()
    {
        $this->locator = new DefaultFileLocator();
    }

    public function testIsAFileLocator()
    {
        $this->assertTrue(is_a(DefaultFileLocator::class, FileLocatorInterface::class, true));
    }

    /**
     * @dataProvider provideAbsolutePaths
     */
    public function testIsAbsolutePath($path)
    {
        $reflectionObject = new \ReflectionObject($this->locator);
        $methodReflection = $reflectionObject->getMethod('isAbsolutePath');
        $methodReflection->setAccessible(true);

        $this->assertTrue(
            $methodReflection->invoke($this->locator, $path),
            '->isAbsolutePath() returns true for an absolute path'
        );
    }

    public function testLocate()
    {
        $this->assertEquals(
            __FILE__,
            $this->locator->locate('FileLocatorTest.php', __DIR__)
        );

        $this->assertEquals(
            __FILE__,
            $this->locator->locate(__DIR__.DIRECTORY_SEPARATOR.'FileLocatorTest.php')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.+?)foobar.xml" does not exist\./
     */
    public function testThrowsAnExceptionIfTheFileDoesNotExists()
    {
        $this->locator->locate('foobar.xml', __DIR__);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.+?)foobar.xml" does not exist\./
     */
    public function testLocateThrowsAnExceptionIfTheFileDoesNotExistsInAbsolutePath()
    {
        $this->locator->locate(__DIR__.'/Fixtures/foobar.xml');
    }

    public function provideAbsolutePaths()
    {
        return [
            ['/foo.xml'],
            ['c:\\\\foo.xml'],
            ['c:/foo.xml'],
            ['\\server\\foo.xml'],
            ['https://server/foo.xml'],
            ['phar://server/foo.xml'],
        ];
    }
}
