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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use InvalidArgumentException;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FixtureBagDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SimpleFixtureBagDenormalizer
 */
class SimpleFixtureBagDenormalizerTest extends TestCase
{
    public function testIsAFixtureBagDenormalizer()
    {
        $this->assertTrue(is_a(SimpleFixtureBagDenormalizer::class, FixtureBagDenormalizerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleFixtureBagDenormalizer::class))->isCloneable());
    }

    public function testDenormalizesASetOfDataIntoAFixtureBag()
    {
        $fixture1Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture1Prophecy->getId()->willReturn('user_alice');
        /** @var FixtureInterface $fixture1 */
        $fixture1 = $fixture1Prophecy->reveal();

        $fixture2Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture2Prophecy->getId()->willReturn('user_bob');
        /** @var FixtureInterface $fixture2 */
        $fixture2 = $fixture2Prophecy->reveal();

        $fixture3Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture3Prophecy->getId()->willReturn('owern1');
        /** @var FixtureInterface $fixture3 */
        $fixture3 = $fixture3Prophecy->reveal();

        $fixture4Prophecy = $this->prophesize(FixtureInterface::class);
        $fixture4Prophecy->getId()->willReturn('owern2');
        /** @var FixtureInterface $fixture4 */
        $fixture4 = $fixture3Prophecy->reveal();

        $data = [
            'Nelmio\Entity\User (dummy_flag)' => [
                'user_alice' => [
                    'username' => 'alice',
                ],
                'user_bob' => [
                    'username' => 'bob',
                ],
            ],
            'Nelmio\Entity\Owner' => [
                'owner1' => [],
                'owner2' => null,
            ],
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $userFlags = (new FlagBag('Nelmio\Alice\Entity\User'))->withFlag(new ElementFlag('dummy_flag'));
        $flagParserProphecy
            ->parse('Nelmio\Entity\User (dummy_flag)')
            ->willReturn($userFlags)
        ;
        $ownerFlags = new FlagBag('Nelmio\Entity\Owner');
        $flagParserProphecy
            ->parse('Nelmio\Entity\Owner')
            ->willReturn(new FlagBag('Nelmio\Entity\Owner'))
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $fixtureDenormalizerProphecy = $this->prophesize(FixtureDenormalizerInterface::class);
        $newFixtureBag = new FixtureBag();
        $bag1 = $newFixtureBag->with($fixture1);
        $fixtureDenormalizerProphecy
            ->denormalize(
                new FixtureBag(),
                'Nelmio\Alice\Entity\User',
                'user_alice',
                [
                    'username' => 'alice'
                ],
                $userFlags
            )
            ->willReturn($bag1)
        ;
        $bag2 = $bag1->with($fixture2);
        $fixtureDenormalizerProphecy
            ->denormalize(
                $bag1,
                'Nelmio\Alice\Entity\User',
                'user_bob',
                [
                    'username' => 'bob'
                ],
                $userFlags
            )
            ->willReturn($bag2)
        ;
        $bag3 = $bag2->with($fixture3);
        $fixtureDenormalizerProphecy
            ->denormalize(
                $bag2,
                'Nelmio\Entity\Owner',
                'owner1',
                [],
                $ownerFlags
            )
            ->willReturn($bag3)
        ;
        $bag4 = $bag3->with($fixture4);
        $fixtureDenormalizerProphecy
            ->denormalize(
                $bag3,
                'Nelmio\Entity\Owner',
                'owner2',
                [],
                $ownerFlags
            )
            ->willReturn($bag4)
        ;
        /** @var FixtureDenormalizerInterface $fixtureDenormalizer */
        $fixtureDenormalizer = $fixtureDenormalizerProphecy->reveal();

        $denormalizer = new SimpleFixtureBagDenormalizer($fixtureDenormalizer, $flagParser);
        $actual = $denormalizer->denormalize($data);

        $this->assertSame($bag4, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
        $fixtureDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(4);
    }

    public function testThrowsAnExceptionIfInvalidRawDataFixtureSetGiven()
    {
        $data = [
            'Nelmio\Entity\User' => 'something',
        ];

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $userFlags = new FlagBag('Nelmio\Alice\Entity\User');
        $flagParserProphecy
            ->parse('Nelmio\Entity\User')
            ->willReturn($userFlags)
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new SimpleFixtureBagDenormalizer(new FakeFixtureDenormalizer(), $flagParser);

        try {
            $denormalizer->denormalize($data);

            $this->fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected an array for the class "Nelmio\Entity\User", found "string" instead.',
                $exception->getMessage()
            );
        }

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
