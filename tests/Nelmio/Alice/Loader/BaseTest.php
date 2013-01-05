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
    const CONTACT = 'Nelmio\Alice\fixtures\Contact';

    protected $orm;
    protected $loader;

    protected function loadData(array $data, array $options = array())
    {
        $loader = $this->createLoader($options);

        return $loader->load($data, $this->orm);
    }

    protected function createLoader(array $options = array())
    {
        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
        );
        $options = array_merge($defaults, $options);

        $this->orm = new TestORM;

        return $this->loader = new Base($options['locale'], $options['providers']);
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

    public function testLoadInvalidFile()
    {
        try {
            $res = $this->createLoader()->load($file = __DIR__.'/../fixtures/complete.yml');
        } catch (\UnexpectedValueException $e) {
            $this->assertEquals('Included file "'.$file.'" must return an array of data', $e->getMessage());
        }
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

    public function testLoadParsesPropertyReferences()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'alice',
                ),
                'user2' => array(
                    'username' => '@user1->username',
                ),
            )
        ));

        $this->assertInstanceOf(self::USER, $res[0]);
        $this->assertInstanceOf(self::USER, $res[1]);
        $this->assertEquals('alice', $res[0]->username);
        $this->assertEquals($res[0]->username, $res[1]->username);
    }

    public function testLoadParsesPropertyReferencesGetter()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'alice',
                ),
                'user2' => array(
                    'favoriteNumber' => '@user1->age',
                ),
            )
        ));

        $this->assertInstanceOf(self::USER, $res[0]);
        $this->assertInstanceOf(self::USER, $res[1]);
        $this->assertEquals($res[0]->getAge(), $res[1]->favoriteNumber);
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Property doesnotexist is not defined for reference user1
     */
    public function testLoadParsesPropertyReferencesDoesNotExist()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'alice',
                ),
                'user2' => array(
                    'username' => '@user1->doesnotexist',
                ),
            )
        ));
    }

    public function testLoadParsesSingleWildcardReference()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'bob',
                ),
            ),
            self::GROUP => array(
                'a' => array(
                    'owner' => '@user*'
                ),
            ),
        ));

        $group = $res[1];
        $this->assertInstanceOf(self::USER, $group->getOwner());
        $this->assertEquals('bob', $group->getOwner()->username);
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

    public function testLoadCoercesDatesForDateTimeHints()
    {
        $res = $this->loadData(array(
            self::GROUP => array(
                'group0' => array(
                    'creationDate' => '2012-01-05',
                ),
                'group1' => array(
                    'creationDate' => '<unixTime()>',
                ),
            ),
        ));

        $this->assertInstanceOf('DateTime', $res[0]->getCreationDate());
        $this->assertEquals('2012-01-05', $res[0]->getCreationDate()->format('Y-m-d'));
        $this->assertInstanceOf('DateTime', $res[1]->getCreationDate());
    }

    public function testLoadParsesFakerData()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<firstName()>',
                ),
            ),
        ));

        $this->assertNotEquals('<firstName()>', $res[0]->username);
        $this->assertNotEmpty($res[0]->username);
    }

    public function testLoadParsesFakerDataMultiple()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<firstName()> <lastName()>',
                ),
            ),
        ));

        $this->assertNotEquals('<firstName()> <lastName()>', $res[0]->username);
        $this->assertRegExp('{^[\w\']+ [\w\']+$}i', $res[0]->username);
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

    public function testLoadParsesFakerDataWithLocale()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<fr_FR:siren()>',
                ),
            ),
        ));

        $this->assertRegExp('{^\d{3} \d{3} \d{3}$}', $res[0]->username);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown formatter "siren"
     */
    public function testLoadParsesFakerDataUsesDefaultLocale()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<siren()>',
                ),
            ),
        ));
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

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Cannot use <current()> out of fixtures ranges.
     */
    public function testCurrentProviderFailsOutOfRanges()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => '<current()>',
                ),
            ),
        ));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Could not determine how to assign inexistent to a Nelmio\Alice\fixtures\User object.
     */
    public function testArbitraryPropertyNamesFail()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'inexistent' => 'foo',
                ),
            ),
        ));
    }

    public function testLoadBypassesConstructorsWithRequiredArgs()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    'username' => 'alice',
                ),
            ),
            self::CONTACT => array(
                'contact' => array(
                    'user' => '@user',
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertInstanceOf(self::CONTACT, $this->loader->getReference('contact'));
        $this->assertSame(
            $this->loader->getReference('user'),
            $this->loader->getReference('contact')->getUser()
        );
    }

    public function testLoadCallsConstructorIfProvided()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('alice', 'alice@example.com'),
                ),
            ),
            self::CONTACT => array(
                'contact' => array(
                    '__construct' => array('@user'),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertSame('alice', $this->loader->getReference('user')->username);
        $this->assertSame('alice@example.com', $this->loader->getReference('user')->email);
        $this->assertSame(
            $this->loader->getReference('user'),
            $this->loader->getReference('contact')->getUser()
        );
    }

    public function testLoadCallsCustomMethodAfterCtor()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    'doStuff' => array(0, 3, 'bob'),
                    '__construct' => array('alice', 'alice@example.com'),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $res[0]);
        $this->assertSame('bob', $res[0]->username);
        $this->assertSame('alice@example.com', $res[0]->email);
    }

    public function testConstructorCustomProviders()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user0' => array(
                    'username' => '<fooGenerator()>',
                ),
            ),
        ));

        $this->assertEquals('foo', $res[0]->username);
    }

    public function testLoadCallsCustomMethodWithMultipleArgumentsAndCustomProviders()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('<fooGenerator()>', '<fooGenerator()>@example.com'),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $res[0]);
        $this->assertSame('foo', $res[0]->username);
        $this->assertSame('foo@example.com', $res[0]->email);
    }

    public function testLoadCallsConstructorWithHintedParams()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array(null, null, '<dateTimeBetween("-10years", "now")>'),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $res[0]);
        $this->assertInstanceOf('DateTime', $res[0]->birthDate);
    }
}

class FakerProvider
{
    public function fooGenerator()
    {
        return 'foo';
    }
}
