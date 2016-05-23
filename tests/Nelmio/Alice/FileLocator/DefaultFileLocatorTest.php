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
class DefaultFileLocatorTest extends \PHPUnit_Framework_TestCase
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
            $this->locator->locate('DefaultFileLocatorTest.php', __DIR__)
        );

        $this->assertEquals(
            __FILE__,
            $this->locator->locate(__DIR__.DIRECTORY_SEPARATOR.'DefaultFileLocatorTest.php')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage An empty file name is not valid to be located.
     */
    public function testThrowExceptionIfEmptyFileNamePassed()
    {
        $this->locator->locate('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.+?)foobar.xml" does not exist\./
     */
    public function testThrowExceptionIfTheFileDoesNotExists()
    {
        $this->locator->locate('foobar.xml', __DIR__);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.+?)foobar.xml" does not exist\./
     */
    public function testLocateThrowExceptionIfTheFileDoesNotExistsInAbsolutePath()
    {
        $this->locator->locate(__DIR__.'/Fixtures/foobar.xml');
    }

    public function provideAbsolutePaths()
    {
        return [
            ['/foo.xml'],
            ['\\server\\foo.xml'],
            ['c:\\\\foo.xml'],
            ['c:/foo.xml'],
            ['https://server/foo.xml'],
            ['phar://server/foo.xml'],
        ];
    }
}
