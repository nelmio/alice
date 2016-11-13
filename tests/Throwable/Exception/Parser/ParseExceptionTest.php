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

namespace Nelmio\Alice\Throwable\Exception\Parser;

use Nelmio\Alice\Throwable\ParseThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Parser\ParseException
 */
class ParseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(ParseException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(ParseException::class, ParseThrowable::class, true));
    }

    public function testCreateExceptionForInvalidYaml()
    {
        $exception = ParseException::createForInvalidYaml('foo.yml');
        $this->assertEquals(
            'The file "foo.yml" does not contain valid YAML.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 100;
        $previous = new \Error();

        $exception = ParseException::createForInvalidYaml('foo.yml', $code, $previous);
        $this->assertEquals(
            'The file "foo.yml" does not contain valid YAML.',
            $exception->getMessage()
        );
        $this->assertEquals(100, $exception->getCode());
        $this->assertNotNull($exception->getPrevious());
    }

    public function testCreateExceptionForUnparsableFile()
    {
        $exception = ParseException::createForUnparsableFile('foo.php');
        $this->assertEquals(
            'Could not parse the file "foo.php".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 100;
        $previous = new \Error();

        $exception = ParseException::createForUnparsableFile('foo.php', $code, $previous);
        $this->assertEquals(
            'Could not parse the file "foo.php".',
            $exception->getMessage()
        );
        $this->assertEquals(100, $exception->getCode());
        $this->assertNotNull($exception->getPrevious());
    }

    public function testCreateExceptionForUnlocalizableFile()
    {
        $exception = ParseException::createForUnlocalizableFile('foo.php');
        $this->assertEquals(
            'Could not locate the file "foo.php".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 100;
        $previous = new \Error();

        $exception = ParseException::createForUnlocalizableFile('foo.php', $code, $previous);
        $this->assertEquals(
            'Could not locate the file "foo.php".',
            $exception->getMessage()
        );
        $this->assertEquals(100, $exception->getCode());
        $this->assertNotNull($exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildParseException::createForInvalidYaml('foo.yml');
        $this->assertInstanceOf(ChildParseException::class, $exception);

        $exception = ChildParseException::createForUnparsableFile('foo.php');
        $this->assertInstanceOf(ChildParseException::class, $exception);

        $exception = ChildParseException::createForUnlocalizableFile('foo.php');
        $this->assertInstanceOf(ChildParseException::class, $exception);
    }
}

class ChildParseException extends ParseException
{
}
