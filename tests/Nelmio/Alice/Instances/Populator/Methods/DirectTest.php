<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\CompositeCamelCaseDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\CompositeMixedCaseDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\CompositeSnakeCase1Dummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\CompositeSnakeCase2Dummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\CompositeSnakeCase3Dummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\PrivateDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\ProtectedDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\PublicDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\SimpleCamelCaseDummy;
use Nelmio\Alice\Instances\Populator\Fixtures\Direct\SimpleSnakeCaseDummy;
use Nelmio\Alice\Util\TypeHintChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Instances\Populator\Methods\Direct
 */
class DirectTest extends TestCase
{
    /**
     * @var Fixture
     */
    private $fixture;

    /**
     * @var Direct
     */
    private $direct;

    protected function setUp()
    {
        $fixtureProphecy = $this->prophesize('Nelmio\Alice\Fixtures\Fixture');
        $fixtureProphecy->isLocal()->shouldNotBeCalled();
        $this->fixture = $fixtureProphecy->reveal();
        
        $typeHintCheckerProphecy = $this->prophesize('Nelmio\Alice\Util\TypeHintChecker');
        $typeHintCheckerProphecy->check(Argument::cetera())->willReturnArgument(2);
        /** @var TypeHintChecker $typeHintChecker */
        $typeHintChecker = $typeHintCheckerProphecy->reveal();

        $this->direct = new Direct($typeHintChecker);
    }

    /**
     * @dataProvider provideProperties
     * @group legacy
     */
    public function testCanSet($property, $model, $expected)
    {
        $actual = $this->direct->canSet($this->fixture, $model, $property, null);

        $this->assertSame($expected, $actual);
    }

    public function testSetPropertyViaSetter()
    {
        $property = 'name';
        $name = 'John Doe';

        $typeHintCheckerProphecy = $this->prophesize('Nelmio\Alice\Util\TypeHintChecker');
        $typeHintCheckerProphecy->check(Argument::cetera())->willReturnArgument(2);
        /** @var TypeHintChecker $typeHintChecker */
        $typeHintChecker = $typeHintCheckerProphecy->reveal();

        $direct = new Direct($typeHintChecker);

        $modelProphecy = $this->prophesize('Nelmio\Alice\Instances\Populator\Fixtures\Direct\PublicDummy');
        $modelProphecy->setName($name)->shouldBeCalled();
        /** @var PublicDummy $model */
        $model = $modelProphecy->reveal();

        $direct->set($this->fixture, $model, $property, $name);

        $typeHintCheckerProphecy->check(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $modelProphecy->setName(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @dataProvider provideModelsToSet
     */
    public function testSetProperty($model, $property, $value, $expectedProperty)
    {
        $this->direct->set($this->fixture, $model, $property, $value);

        self::assertEquals($value, $model->$expectedProperty);
    }

    /**
     * @dataProvider provideLegacyModelsToSet
     */
    public function testLegacySetProperty($model, $property, $value, $expectedProperty)
    {
        $this->direct->set($this->fixture, $model, $property, $value);

        self::assertEquals($value, $model->$expectedProperty);
    }

    /**
     * @group legacy
     */
    public function testSetPropertyViaPrivateSetter()
    {
        $this->direct->set($this->fixture, $model = new PrivateDummy(), 'name', $value = 'John Doe');

        self::assertEquals($value, $model->name);
    }

    /**
     * @group legacy
     */
    public function testSetPropertyViaProtectedSetter()
    {
        $this->direct->set($this->fixture, $model = new ProtectedDummy(), 'name', $value = 'John Doe');

        self::assertEquals($value, $model->name);
    }

    public function provideProperties()
    {
        return [
            'simple property with camelCase setter' => [
                'name',
                new SimpleCamelCaseDummy(),
                true,
            ],
            'simple property with snake_case setter' => [
                'name',
                new SimpleSnakeCaseDummy(),
                true,
            ],

            'composite camelCase property with camelCase setter' => [
                'fullName',
                new CompositeCamelCaseDummy(),
                true,
            ],
            'composite snake_case property with camelCase setter' => [
                'full_name',
                new CompositeCamelCaseDummy(),
                true,
            ],

            'composite camelCase property with snake_case1 setter' => [
                'fullName',
                new CompositeSnakeCase1Dummy(),
                false,
            ],
            'composite snake_case property with snake_case1 setter' => [
                'full_name',
                new CompositeSnakeCase1Dummy(),
                true,
            ],

            'composite camelCase property with snake_case2 setter' => [
                'fullName',
                new CompositeSnakeCase2Dummy(),
                false,
            ],
            'composite snake_case property with snake_case2 setter' => [
                'full_name',
                new CompositeSnakeCase2Dummy(),
                true,
            ],

            'composite camelCase property with snake_case3 setter' => [
                'fullName',
                new CompositeSnakeCase3Dummy(),
                true,
            ],
            'composite snake_case property with snake_case3 setter' => [
                'full_name',
                new CompositeSnakeCase3Dummy(),
                true,
            ],

            'composite camelCase property with mixed setter (BC preserved)' => [
                'fullName',
                new CompositeMixedCaseDummy(),
                true,
            ],
            'composite snake_case property with mixed setter (BC preserved)' => [
                'full_name',
                new CompositeMixedCaseDummy(),
                true,
            ],

            'no setter' => [
                'propertyWithoutAccessor',
                new SimpleCamelCaseDummy(),
                false,
            ],
        ];
    }

    public function provideModelsToSet()
    {
        $value = 'John Doe';

        return [
            'simple property with camelCase setter' => [
                new SimpleCamelCaseDummy(),
                'name',
                $value,
                'name',
            ],

            'composite camelCase property with camelCase setter' => [
                new CompositeCamelCaseDummy(),
                'fullName',
                $value,
                'fullName',
            ],
            'composite snake_case property with camelCase setter' => [
                new CompositeCamelCaseDummy(),
                'full_name',
                $value,
                'fullName',
            ],

            'composite camelCase property with mixed setter (BC preserved)' => [
                new CompositeMixedCaseDummy(),
                'fullName',
                $value,
                'fullname',
            ],
            'composite snake_case property with mixed setter (BC preserved)' => [
                new CompositeMixedCaseDummy(),
                'full_name',
                $value,
                'fullname',
            ],

            'public setter' => [
                new PublicDummy(),
                'name',
                $value,
                'name',
            ],
        ];
    }

    public function provideLegacyModelsToSet()
    {
        $value = 'John Doe';

        return [
            'simple property with snake_case setter' => [
                new SimpleSnakeCaseDummy(),
                'name',
                $value,
                'name',
            ],

            'composite snake_case property with snake_case1 setter' => [
                new CompositeSnakeCase1Dummy(),
                'full_name',
                $value,
                'full_name',
            ],

            'composite snake_case property with snake_case2 setter' => [
                new CompositeSnakeCase2Dummy(),
                'full_name',
                $value,
                'full_name',
            ],

            'composite camelCase property with snake_case3 setter' => [
                new CompositeSnakeCase3Dummy(),
                'fullName',
                $value,
                'full_name',
            ],
            'composite snake_case property with snake_case3 setter' => [
                new CompositeSnakeCase3Dummy(),
                'full_name',
                $value,
                'full_name',
            ],

            'composite camelCase property with mixed setter (BC preserved)' => [
                new CompositeMixedCaseDummy(),
                'fullName',
                $value,
                'fullname',
            ],
            'composite snake_case property with mixed setter (BC preserved)' => [
                new CompositeMixedCaseDummy(),
                'full_name',
                $value,
                'fullname',
            ],

            'public setter' => [
                new PublicDummy(),
                'name',
                $value,
                'name',
            ],
        ];
    }
}
