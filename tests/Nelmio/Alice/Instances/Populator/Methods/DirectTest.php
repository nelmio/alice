<?php
namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\support\models\User;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Instances\Populator\Methods\Direct
 */
class DirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fixture
     */
    private $fixture;

    /**
     * @var Direct
     */
    private $direct;

    /**
     * @var User
     */
    private $user;

    protected function setUp()
    {
        $this->user      = new User();
        $this->fixture   = $this->prophesize('Nelmio\Alice\Fixtures\Fixture')->reveal();
        $typeHintChecker = $this->prophesize('Nelmio\Alice\Util\TypeHintChecker');
        $this->direct    = new Direct($typeHintChecker->reveal());

        $typeHintChecker
            ->check(Argument::any(), Argument::any(), Argument::any())
            ->willReturnArgument(2);
    }

    /**
     * @return array<{bool, string}>
     */
    public function canSetProvider()
    {
        return [
            [false, 'uuid'],            // No setter, property exists
            [true, 'family_name'],      // setFamilyName exists
            [false, 'non-existing'],    // No setter, property does not exist
            [true, 'familyname'],       // setFamilyName exists
            [true, 'favoriteNumber'],   // setFavoriteNumber exists
            [true, 'favoritenumber'],   // methods are case insensitive in PHP
            [true, 'favORite_Number'],  // methods are case insensitive in PHP, and snake case is supported
            [true, 'display_name'],     // when there are two methods, BC is preserved.
        ];
    }

    /**
     * @dataProvider canSetProvider
     * @param bool $expected
     * @param string $property
     */
    public function testCanSet($expected, $property)
    {

        self::assertSame($expected, $this->direct->canSet($this->fixture, $this->user, $property, null));
    }

    /**
     * @return array<{string, string, mixed}>
     */
    public function setProvider()
    {
        return [
            ['username',     'username', 'alice'],
            ['user_name',    'username', 'bill'],
            ['UsEr_N_a_m_e', 'username', 'tweedle-dee'],
            ['family_name',  'family_name', 'Dee'],
            ['familyname',   'family_name', 'Dum'],
            ['familyName',   'family_name', 'Wonderland'],
            ['display_name', 'display_name', 'Hatter'],
        ];
    }

    /**
     * @dataProvider setProvider
     * @param $expectedProperty
     * @param $fixtureProperty
     * @param $value
     */
    public function testSet($fixtureProperty, $expectedProperty, $value)
    {
        $this->direct->set($this->fixture, $this->user, $fixtureProperty, $value);
        self::assertSame($value, $this->user->$expectedProperty);
    }
}
