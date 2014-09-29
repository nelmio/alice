<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

class FixtureTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

    public function testWillParseFlagsOutOfTheClass()
    {
        $fixture = new Fixture(self::USER.' (local)', 'user', array(), null);

        $this->assertEquals(self::USER, $fixture->getClass());
    }

    public function testWillParseFlagsOutOfTheName()
    {
        $fixture = new Fixture(self::USER, 'user (local)', array(), null);

        $this->assertEquals('user', $fixture->getName());
    }

    public function testIsLocalWithLocalClassFlag()
    {
        $fixture = new Fixture(self::USER.' (local)', 'user', array(), null);

        $this->assertTrue($fixture->isLocal());
    }

    public function testIsLocalWithLocalNameFlag()
    {
        $fixture = new Fixture(self::USER, 'user (local)', array(), null);

        $this->assertTrue($fixture->isLocal());
    }

    public function testIsNotLocalWithNeitherClassNorNameFlag()
    {
        $fixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertFalse($fixture->isLocal());
    }

    public function testIsTemplateWithTemplateNameFlag()
    {
        $fixture = new Fixture(self::USER, 'user (template)', array(), null);

        $this->assertTrue($fixture->isTemplate());
    }

    public function testIsNotTemplateWithoutTemplateNameFlag()
    {
        $fixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertFalse($fixture->isTemplate());
    }

    public function testIsNotTemplateWithExtendsNameFlag($value='')
    {
        $fixture = new Fixture(self::USER, 'user (extends user_template)', array(), null);

        $this->assertFalse($fixture->isTemplate());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Argument must be a template, not just a fixture.
     */
    public function testExtendTemplateRequiresThatTheArgumentIsATemplate()
    {
        $fixture1 = new Fixture(self::USER, 'user1', array(), null);
        $fixture2 = new Fixture(self::USER, 'user2', array(), null);
        $fixture1->extendTemplate($fixture2);
    }

    public function testExtendTemplateWillMapUnsetPropertiesOnTheFixture()
    {
        $template = new Fixture(self::USER, 'user_full (template)', array('name' => 'John Doe', 'email' => 'john@doe.org'), null);
        $fixture = new Fixture(self::USER, 'user', array('email' => 'jane@doe.org'), null);

        $fixture->extendTemplate($template);
        $properties = $fixture->getProperties();
        $this->assertEquals('John Doe', $properties['name']->getValue());
    }

    public function testExtendTemplateWillNotMapSetPropertiesOnTheFixture()
    {
        $template = new Fixture(self::USER, 'user_full (template)', array('name' => 'John Doe', 'email' => 'john@doe.org'), null);
        $fixture = new Fixture(self::USER, 'user', array('email' => 'jane@doe.org'), null);

        $fixture->extendTemplate($template);
        $properties = $fixture->getProperties();
        $this->assertEquals('jane@doe.org', $properties['email']->getValue());
    }

    public function testGetExtensionsReturnsAListOfAllTemplateNamesTheFixtureExtends()
    {
        $fixture = new Fixture(self::USER, 'user (extends user_name, extends user_email)', array(), null);

        $this->assertEquals(array('user_name', 'user_email'), $fixture->getExtensions());
    }

    public function testHasExtensionsIsFalseWhenNoExtensionsExist()
    {
        $fixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertFalse($fixture->hasExtensions());
    }

    public function testHasExtensionsIsTrueWhenExtensionsExist()
    {
        $fixture = new Fixture(self::USER, 'user (extends user_name, extends user_email)', array(), null);

        $this->assertTrue($fixture->hasExtensions());
    }

    public function testGetPropertiesWillReturnOnlyBasicValueProperties()
    {
        $fixture = new Fixture(self::USER, 'user', array('name' => 'John Doe', 'email' => 'john@doe.org', '__construct' => array('1', '2'), '__set' => 'setterFunc'), null);

        $properties = $fixture->getProperties();
        $this->assertEquals(array('name' => $properties['name'], 'email' => $properties['email']), $fixture->getProperties());
    }

    public function testHasClassFlagWillReturnIfClassFLagExists()
    {
        $fixture = new Fixture(self::USER.' (local)', 'user', array(), null);

        $this->assertTrue($fixture->hasClassFlag('local'));
        $this->assertFalse($fixture->hasClassFlag('badname'));
    }

    public function testHasNameFlagWillReturnIfNameFLagExists()
    {
        $fixture = new Fixture(self::USER, 'user (local)', array(), null);

        $this->assertTrue($fixture->hasNameFlag('local'));
        $this->assertFalse($fixture->hasNameFlag('badname'));
    }

    public function testGetConstructorMethodWillReturnTheMethodName()
    {
        $fixture = new Fixture(self::USER, 'user', array('__construct' => array('create' => array('1', '2', '3'))), null);

        $this->assertEquals('create', $fixture->getConstructorMethod());
    }

    public function testGetConstructorArgsWillReturnTheArgumentsList()
    {
        $fixture = new Fixture(self::USER, 'user', array('__construct' => array('create' => array('1', '2', '3'))), null);

        $this->assertEquals(array('1', '2', '3'), $fixture->getConstructorArgs());
    }

    public function testShouldUseConstructorWillReturnTrueIfThereIsNoConstructorInTheSpec()
    {
        $fixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertTrue($fixture->shouldUseConstructor());
    }

    public function testShouldUseConstructorWillReturnFalseIfTheConstructorSpecIsFalse()
    {
        $fixture = new Fixture(self::USER, 'user', array('__construct' => false), null);

        $this->assertFalse($fixture->shouldUseConstructor());
    }

    public function testShouldUseConstructorWillReturnTrueIfTheConstructorSpecIsDefined()
    {
        $fixture = new Fixture(self::USER, 'user', array('__construct' => array('1', '2')), null);

        $this->assertTrue($fixture->shouldUseConstructor());
    }

    public function testHasCustomerSetterWillReturnIfTheSpecDefinesACustomSetter()
    {
        $setFixture = new Fixture(self::USER, 'user', array('__set' => 'setterFunc'), null);
        $noSetFixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertTrue($setFixture->hasCustomSetter());
        $this->assertFalse($noSetFixture->hasCustomSetter());
    }

    public function testGetCustomSetterWillReturnTheCustomSetterValue()
    {
        $setFixture = new Fixture(self::USER, 'user', array('__set' => 'setterFunc'), null);
        $noSetFixture = new Fixture(self::USER, 'user', array(), null);

        $this->assertEquals('setterFunc', $setFixture->getCustomSetter());
        $this->assertNull($noSetFixture->getCustomSetter());
    }
}
