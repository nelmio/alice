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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserExceptionFactory
 */
class FlagParserExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTestCreateNewException()
    {
        $exception = FlagParserExceptionFactory::createForNoParserFoundForElement('foo');

        $this->assertEquals(
            'No suitable flag parser found to handle the element "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateNewExceptionForUnexpectedCall()
    {
        $exception = FlagParserExceptionFactory::createForExpectedMethodToBeCalledIfHasAParser('foo');

        $this->assertEquals(
            'Expected method "foo" to be called only if it has a flag parser.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

}
