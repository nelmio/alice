<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

use Nelmio\Alice\Definition\Flag\AnotherDummyFlag;
use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\MutableFlag;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

/**
 * @covers Nelmio\Alice\Definition\FlagBag
 */
class FlagBagTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $flags = new FlagBag('user0');

        $this->assertEquals('user0', $flags->getKey());
    }

    /**
     * @depends testWithersReturnNewModifiedInstance
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $flag = new MutableFlag('flag0');
        $flags = new FlagBag('user0');
        $newFlags = $flags->with($flag);

        $this->assertInstanceOf(FlagBag::class, $flags);
        $this->assertNotSame($flags, $newFlags);

        $this->assertCount(0, $flags);
        $this->assertCount(1, $newFlags);

        // Mutate injected value
        $flag->setValue('flag1');

        // Mutate return value
        foreach ($flags as $flag) {
            $flag->setvalue('flag2');
        }

        $this->assertEquals(
            $flags->with(new MutableFlag('flag0')),
            $newFlags
        );

        $anotherBag = (new FlagBag('user2'))->with(new MutableFlag('another_flag0'));
        $mergedBag = $newFlags->mergeWith($anotherBag);

        $this->assertInstanceOf(FlagBag::class, $mergedBag);
        $this->assertEquals('user0', $mergedBag->getKey(), 'Expected original key to be kept.');
        $this->assertCount(2, $mergedBag);

        // Mutate injected value
        $flag->setValue('another_flag1');

        // Mutate return value
        foreach ($flags as $flag) {
            $flag->setvalue('another_flag2');
        }

        $this->assertEquals(
            $newFlags->mergeWith($anotherBag),
            $mergedBag
        );
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
        $flag1 = new DummyFlag();
        $flag2 = new AnotherDummyFlag();

        $flags = (new FlagBag('user0'))
            ->with($flag1)
            ->with($flag2)
        ;

        $this->assertSameFlags(
            [
                $flag1,
                $flag2,
            ],
            $flags
        );
    }

    public function testCanCumulateExtendFlags()
    {
        $extendFlag1 = new ExtendFlag(new FixtureReference('user_base'));
        $extendFlag2 = new ExtendFlag(new FixtureReference('user_with_owner'));

        $flags = (new FlagBag('user0'))
            ->with($extendFlag1)
            ->with($extendFlag2)
        ;

        $this->assertSameFlags(
            [
                $extendFlag1,
                $extendFlag2,
            ],
            $flags
        );
    }

    public function testCannotCumulateOptionalFlags()
    {
        $optionalFlag1 = new OptionalFlag(20);
        $optionalFlag2 = new OptionalFlag(60);

        $flags = (new FlagBag('user0'))
            ->with($optionalFlag1)
            ->with($optionalFlag2)
        ;

        $this->assertSameFlags(
            [
                $optionalFlag2,
            ],
            $flags
        );
    }

    public function testCannotCumulateTemplateFlags()
    {
        $templateFlag1 = new TemplateFlag();
        $templateFlag2 = new TemplateFlag();

        $flags = (new FlagBag('user0'))
            ->with($templateFlag1)
            ->with($templateFlag2)
        ;

        $this->assertSameFlags(
            [
                $templateFlag2,
            ],
            $flags
        );
    }

    public function testCannotCumulateUniqueFlags()
    {
        $uniqueFlag1 = new UniqueFlag();
        $uniqueFlag2 = new UniqueFlag();

        $flags = (new FlagBag('user0'))
            ->with($uniqueFlag1)
            ->with($uniqueFlag2)
        ;

        $this->assertSameFlags(
            [
                $uniqueFlag2,
            ],
            $flags
        );
    }

    private function assertSameFlags(array $expected, FlagBag $actual)
    {
        $flags = [];
        foreach ($actual as $key => $value) {
            $flags[$key] = $value;
        }

        $this->assertEquals($expected, $flags);
    }
}
