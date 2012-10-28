<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\TestORM;
use Nelmio\Alice\Loader\Base;
use Nelmio\Alice\fixtures\User;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\fixtures\User';
    const GROUP = 'Nelmio\Alice\fixtures\Group';

    protected $orm;
    protected $loader;

    protected function loadData(array $data, array $options = array())
    {
        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
        );
        $options = array_merge($defaults, $options);

        $this->orm = new TestORM;
        $this->loader = new Base($options['locale'], $options['providers']);

        return $this->loader->load($data, $this->orm);
    }

    public function testLoadCreatesInstances()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(),
            ),
        ));

        $user = $res[0];
        $this->assertInstanceOf(self::USER, $user);
    }

    public function testGetReference()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(),
            ),
        ));

        $user = $res[0];
        $this->assertSame($user, $this->loader->getReference('bob'));
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Reference foo is not defined
     */
    public function testGetBadReference()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(),
            ),
        ));

        $this->loader->getReference('foo');
    }

    public function testGetReferences()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(),
            ),
        ));

        $this->assertSame($res, array_values($this->loader->getReferences()));
    }

    public function testLoadAssignsDataToProperties()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(
                    'username' => 'bob'
                ),
            ),
        ));

        $user = $res[0];
        $this->assertEquals('bob', $user->username);
    }

    public function testLoadAssignsDataToSetters()
    {
        $res = $this->loadData(array(
            self::GROUP => array(
                'a' => array(
                    'name' => 'group'
                ),
            ),
        ));

        $group = $res[0];
        $this->assertEquals('group', $group->getName());
    }

    public function testLoadAddsReferencesToAdders()
    {
        $res = $this->loadData(array(
            self::GROUP => array(
                'a' => array(
                    'members' => array($user = new User())
                ),
            ),
        ));

        $group = $res[0];
        $this->assertSame($user, current($group->getMembers()));
    }

    public function testLoadParsesReferences()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'alice',
                ),
            ),
            self::GROUP => array(
                'a' => array(
                    'members' => array('@user1')
                ),
            ),
        ));

        $group = $res[1];
        $this->assertInstanceOf(self::USER, current($group->getMembers()));
        $this->assertEquals('alice', current($group->getMembers())->username);
    }

    public function testLoadParsesMultiReferences()
    {
        $usernames = range('a', 'z');
        $data = array();
        foreach ($usernames as $key => $username) {
            $data[self::USER]['user'.$key]['username'] = $username;
        }
        $data[self::GROUP]['a']['members'] = '5x @user*';
        $res = $this->loadData($data);

        $group = $this->loader->getReference('a');
        $this->assertCount(5, $group->getMembers());
        foreach ($group->getMembers() as $member) {
            $this->assertContains($member->username, $usernames);
        }
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Reference mask "user*" did not match any existing reference, make sure the object is created after its references
     */
    public function testLoadFailsMultiReferencesIfNoneMatch()
    {
        $usernames = range('a', 'z');
        $data = array(
            self::GROUP => array(
                'a' => array(
                    'members' => '5x @user*',
                ),
            ),
        );
        $res = $this->loadData($data);
    }

    public function testLoadParsesMultiReferencesAndOnlyPicksUniques()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'alice',
                ),
            ),
            self::GROUP => array(
                'a' => array(
                    'members' => '5x @user*',
                ),
            ),
        ));

        $group = $res[1];
        $this->assertCount(1, $group->getMembers());
    }

    public function testLoadParsesOptionalValuesWithPercents()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '50%? name',
                ),
                'user1' => array(
                    'username' => '50%? name : nothing',
                ),
                'user2' => array(
                    'username' => '0%? name : nothing',
                ),
                'user3' => array(
                    'username' => '100%? name : nothing',
                ),
            ),
        ));

        $this->assertContains($this->loader->getReference('user0')->username, array('name', null));
        $this->assertContains($this->loader->getReference('user1')->username, array('name', 'nothing'));
        $this->assertEquals('nothing', $this->loader->getReference('user2')->username);
        $this->assertEquals('name', $this->loader->getReference('user3')->username);
    }

    public function testLoadParsesOptionalValuesWithFloats()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '0.5? name',
                ),
                'user1' => array(
                    'username' => '0.5? name : nothing',
                ),
                'user2' => array(
                    'username' => '0? name : nothing',
                ),
                'user3' => array(
                    'username' => '1? name : nothing',
                ),
            ),
        ));

        $this->assertContains($this->loader->getReference('user0')->username, array('name', null));
        $this->assertContains($this->loader->getReference('user1')->username, array('name', 'nothing'));
        $this->assertEquals('nothing', $this->loader->getReference('user2')->username);
        $this->assertEquals('name', $this->loader->getReference('user3')->username);
    }

    public function testLoadParsesFakerData()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<firstName>',
                ),
            ),
        ));

        $this->assertNotEquals('<firstName>', $res[0]->username);
        $this->assertNotEmpty('<firstName>', $res[0]->username);
    }

    public function testLoadParsesFakerDataMultiple()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<firstName> <lastName>',
                ),
            ),
        ));

        $this->assertNotEquals('<firstName> <lastName>', $res[0]->username);
        $this->assertRegExp('{^[a-z]+ [a-z]+$}i', $res[0]->username);
    }

    public function testLoadParsesFakerDataWithArgs()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<dateTimeBetween("yesterday", "tomorrow")>',
                ),
            ),
        ));

        $this->assertInstanceOf('DateTime', $res[0]->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res[0]->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res[0]->username->getTimestamp());
    }

    public function testLoadParsesFakerDataWithPhpArgs()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<dateTimeBetween("yest"."erday", strrev("omot")."rrow")>',
                ),
            ),
        ));

        $this->assertInstanceOf('DateTime', $res[0]->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res[0]->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res[0]->username->getTimestamp());
    }

    public function testLoadParsesVariables()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<dateTimeBetween("-20days", "-10days")>',
                    'fullname' => '<dateTimeBetween($username, "-9days")>',
                ),
            ),
        ));

        $this->assertInstanceOf('DateTime', $res[0]->fullname);
        $this->assertGreaterThanOrEqual(strtotime("-20days"), $res[0]->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("-9days"), $res[0]->fullname->getTimestamp());
    }

    public function testLoadCreatesInclusiveRangesOfObjects()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user{0..10}' => array(
                    'username' => 'alice',
                ),
            ),
        ));

        $this->assertCount(11, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user0'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user10'));
    }

    public function testLoadCreatesExclusiveRangesOfObjects()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user{0...10}' => array(
                    'username' => 'alice',
                ),
            ),
        ));

        $this->assertCount(10, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user0'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9'));
    }

    public function testLoadSwapsRanges()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user{10..9}' => array(
                    'username' => 'alice',
                ),
            ),
        ));

        $this->assertCount(2, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user10'));
    }

    public function testConstructorCustomProviders()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<fooGenerator>',
                ),
            ),
        ));

        $this->assertEquals('foo', $res[0]->username);
    }
}

class FakerProvider
{
    public function fooGenerator()
    {
        return 'foo';
    }
}