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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Parser\ParseExceptionFactory
 */
class ParseExceptionFactoryTest extends TestCase
{
    public function testCreateForParserNoFoundForFile()
    {
        $exception = ParseExceptionFactory::createForParserNoFoundForFile('foo');

        $this->assertEquals(
            'No suitable parser found for the file "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableFile()
    {
        $exception = ParseExceptionFactory::createForUnparsableFile('foo');

        $this->assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = ParseExceptionFactory::createForUnparsableFile('foo', $code, $previous);

        $this->assertEquals(
            'Could not parse the file "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidYaml()
    {
        $exception = ParseExceptionFactory::createForInvalidYaml('foo');

        $this->assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = ParseExceptionFactory::createForInvalidYaml('foo', $code, $previous);

        $this->assertEquals(
            'The file "foo" does not contain valid YAML.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
