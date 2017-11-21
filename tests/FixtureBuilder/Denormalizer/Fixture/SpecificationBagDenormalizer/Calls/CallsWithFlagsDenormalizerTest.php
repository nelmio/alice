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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\CallsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\Calls\CallsWithFlagsDenormalizer
 */
class CallsWithFlagsDenormalizerTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(CallsWithFlagsDenormalizer::class))->isCloneable());
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /must be an instance of Nelmio\\Alice\\FixtureBuilder\\Denormalizer\\Fixture\\SpecificationBagDenormalizer\\Calls\\MethodFlagHandler\, instance of stdClass given/
     */
    public function testCannotAcceptInvalidMethodHandlers()
    {
        new CallsWithFlagsDenormalizer(
            new FakeCallsDenormalizer(),
            [
                new stdClass(),
            ]
        );
    }

    public function testDenomalizeTheCallsWithoutAnyMethodHandler()
    {
        $fixture = new FakeFixture();

        $unparsedMethod = 'unparsed_method';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse($unparsedMethod)->willReturn(new FlagBag('parsed_method'));
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize(
                $fixture,
                $flagParser,
                'parsed_method',
                $unparsedArguments
            )
            ->willReturn(
                $expected = new FakeMethodCall()
            )
        ;
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();

        $denormalizer = new CallsWithFlagsDenormalizer($callsDenormalizer, []);
        $actual = $denormalizer->denormalize($fixture, $flagParser, $unparsedMethod, $unparsedArguments);

        $this->assertSame($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $callsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizesMethodMethodHandlers()
    {
        $fixture = new FakeFixture();

        $unparsedMethod = 'unparsed_method';
        $unparsedArguments = [
            '<latitude()>',
            '<longitude()>',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($unparsedMethod)
            ->willReturn(
                $flags = (new FlagBag('parsed_method'))
                    ->withFlag(
                        $dummyFlag = new DummyFlag()
                    )
                    ->withFlag(
                        $elem1Flag = new ElementFlag('elem1')
                    )
                    ->withFlag(
                        $elem2Flag = new ElementFlag('elem2')
                    )
            )
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $callsDenormalizerProphecy = $this->prophesize(CallsDenormalizerInterface::class);
        $callsDenormalizerProphecy
            ->denormalize(
                $fixture,
                $flagParser,
                'parsed_method',
                $unparsedArguments
            )
            ->willReturn(
                $denormalizedCall = new FakeMethodCall()
            )
        ;
        /** @var CallsDenormalizerInterface $callsDenormalizer */
        $callsDenormalizer = $callsDenormalizerProphecy->reveal();
        
        $returnMethodUnchanged = function (array $args) {
            return $args[0];
        };
        
        $dummyFlagMethodHandlerProphecy = $this->prophesize(MethodFlagHandler::class);
        $dummyFlagMethodHandlerProphecy
            ->handleMethodFlags(
                $denormalizedCall,
                $dummyFlag
            )
            ->willReturn(
                $methodAfterDummyFlagMethodHandler = new SimpleMethodCall('method_after_dummy_flag_method_handler')
            )
        ;
        $dummyFlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterDummyFlagMethodHandler,
                $elem1Flag
            )
            ->will($returnMethodUnchanged)
        ;
        $dummyFlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterDummyFlagMethodHandler,
                $elem2Flag
            )
            ->will($returnMethodUnchanged)
        ;
        /** @var MethodFlagHandler $dummyFlagMethodHandler */
        $dummyFlagMethodHandler = $dummyFlagMethodHandlerProphecy->reveal();

        $elem1FlagMethodHandlerProphecy = $this->prophesize(MethodFlagHandler::class);
        $elem1FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterDummyFlagMethodHandler,
                $dummyFlag
            )
            ->will($returnMethodUnchanged)
        ;
        $elem1FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterDummyFlagMethodHandler,
                $elem1Flag
            )
            ->willReturn(
                $methodAfterElem1FlagMethodHandler = new SimpleMethodCall('method_after_elem1_flag_method_handler')
            )
        ;
        $elem1FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterElem1FlagMethodHandler,
                $elem2Flag
            )
            ->will($returnMethodUnchanged)
        ;
        /** @var MethodFlagHandler $elem1FlagMethodHandler */
        $elem1FlagMethodHandler = $elem1FlagMethodHandlerProphecy->reveal();

        $elem2FlagMethodHandlerProphecy = $this->prophesize(MethodFlagHandler::class);
        $elem2FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterElem1FlagMethodHandler,
                $dummyFlag
            )
            ->will($returnMethodUnchanged)
        ;
        $elem2FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterElem1FlagMethodHandler,
                $elem1Flag
            )
            ->will($returnMethodUnchanged)
        ;
        $elem2FlagMethodHandlerProphecy
            ->handleMethodFlags(
                $methodAfterElem1FlagMethodHandler,
                $elem2Flag
            )
            ->will($returnMethodUnchanged)
        ;
        /** @var MethodFlagHandler $elem2FlagMethodHandler */
        $elem2FlagMethodHandler = $elem2FlagMethodHandlerProphecy->reveal();

        $expected = $methodAfterElem1FlagMethodHandler;

        $denormalizer = new CallsWithFlagsDenormalizer(
            $callsDenormalizer,
            [
                $dummyFlagMethodHandler,
                $elem1FlagMethodHandler,
                $elem2FlagMethodHandler,
            ]
        );

        $actual = $denormalizer->denormalize($fixture, $flagParser, $unparsedMethod, $unparsedArguments);

        $this->assertSame($expected, $actual);
    }
}
