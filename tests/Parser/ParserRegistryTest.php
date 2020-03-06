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

use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\Throwable\Exception\Parser\ParserNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Parser\ParserRegistry
 */
class ParserRegistryTest extends TestCase
{
    public function testIsAParser()
    {
        $this->assertTrue(is_a(ParserRegistry::class, ParserInterface::class, true));
    }

    public function testAcceptChainableParsers()
    {
        $parserProphecy = $this->prophesize(ChainableParserInterface::class);
        $parserProphecy->canParse(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParserInterface $parser */
        $parser = $parserProphecy->reveal();

        new ParserRegistry([$parser]);
    }

    public function testThrowsAnExceptionIfInvalidParserIsPassed()
    {
        $this->expectException(\TypeError::class);

        new ParserRegistry([new stdClass()]);
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ParserRegistry::class))->isCloneable());
    }

    public function testIteratesOverEveryParsersAndUseTheFirstValidOne()
    {
        $file = 'dummy.php';
        $expected = [new stdClass()];

        $parser1Prophecy = $this->prophesize(ChainableParserInterface::class);
        $parser1Prophecy->canParse($file)->willReturn(false);
        /* @var ChainableParserInterface $parser1 */
        $parser1 = $parser1Prophecy->reveal();

        $parser2Prophecy = $this->prophesize(ChainableParserInterface::class);
        $parser2Prophecy->canParse($file)->willReturn(true);
        $parser2Prophecy->parse($file)->willReturn($expected);
        /* @var ChainableParserInterface $parser2 */
        $parser2 = $parser2Prophecy->reveal();

        $parser3Prophecy = $this->prophesize(ChainableParserInterface::class);
        $parser3Prophecy->canParse(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParserInterface $parser3 */
        $parser3 = $parser3Prophecy->reveal();

        $registry = new ParserRegistry([
            $parser1,
            $parser2,
            $parser3,
        ]);
        $actual = $registry->parse($file);

        $this->assertSame($expected, $actual);

        $parser1Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnExceptionIfNoSuitableParserIsFound()
    {
        $registry = new ParserRegistry([]);

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('No suitable parser found for the file "dummy.php".');

        $registry->parse('dummy.php');
    }
}
