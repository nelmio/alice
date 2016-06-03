<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * @covers Nelmio\Alice\Fixture\FlagBag
 */
class FlagBagTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $flags = new FlagBag('user0');

        $this->assertEquals('user0', $flags->getKey());
    }

    public function testImmutableMutator()
    {
        $flags = new FlagBag('user0');
        $newFlags = $flags->with(new DummyFlag());

        $this->assertInstanceOf(FlagBag::class, $flags);
        $this->assertNotSame($flags, $newFlags);

        $this->assertCount(0, $flags);
        $this->assertCount(1, $newFlags);

        $anotherBag = (new FlagBag('user2'))->with(new AnotherDummyFlag());
        $mergedBag = $newFlags->mergeWith($anotherBag);

        $this->assertInstanceOf(FlagBag::class, $mergedBag);
        $this->assertEquals('user0', $mergedBag->getKey());
        $this->assertCount(2, $mergedBag);
    }

    public function testIsCountable()
    {
        $flags = new FlagBag('user0');
        $this->assertEquals(0, count($flags));

        $flags = $flags->with(new DummyFlag());
        $this->assertEquals(1, count($flags));
    }

    public function testDoesNotDuplicateFlags()
    {
        $flags = (new FlagBag('user0'))
            ->with(new DummyFlag())
            ->with(new DummyFlag())
            ->with(new AnotherDummyFlag())
            ->with(new AnotherDummyFlag())
        ;

        $this->assertCount(2, $flags);
    }

    public function testIsIterable()
    {
        $flags = (new FlagBag('user0'))
            ->with(new DummyFlag())
            ->with(new AnotherDummyFlag())
        ;

        $i = 0;
        foreach ($flags as $flag) {
            $this->assertInstanceOf(FlagInterface::class, $flag);
            /* @var FlagInterface $flag */
            if (0 === $i) {
                $this->assertEquals('dummy_flag', $flag->__toString());
                $i++;

                continue;
            }

            if (1 === $i) {
                $this->assertEquals('another_dummy_flag', $flag->__toString());
                $i++;

                continue;
            }
        }
    }

    public function testIsDeepClonable()
    {
        $this->markTestIncomplete('No true yet.');
        $flags = (new FlagBag('dummy'))->with(new DummyFlag());
        $clone = clone $flags;

        $this->assertNotSame($flags, $clone);
        foreach ($flags as $flag) {
            foreach ($clone as $cloneFlag) {
                $this->assertNotSame($flag, $cloneFlag);
            }
        }
    }
}

class DummyFlag implements FlagInterface
{
    public function __toString(): string
    {
        return 'dummy_flag';
    }
}

class AnotherDummyFlag implements FlagInterface
{
    public function __toString(): string
    {
        return 'another_dummy_flag';
    }
}
