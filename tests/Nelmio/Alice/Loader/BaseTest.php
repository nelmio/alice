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
    const TOPIC = 'Nelmio\Alice\fixtures\Topic';
    const CATEGORY = 'Nelmio\Alice\fixtures\Category';

    protected $orm;

    /**
     * @var \Nelmio\Alice\Loader\Base
     */
    protected $loader;

    protected function loadData(array $data, array $options = array())
    {
        $loader = $this->createLoader($options);

        if (in_array('enableForwardReferences', $options) && $options['enableForwardReferences']) {
            $loader->enableForwardReferences();
        }

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
        $user = $res['bob'];

        $this->assertInstanceOf(self::USER, $user);
    }

    public function testGetReference()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'bob' => array(),
            ),
        ));
        $user = $res['bob'];

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
        $references = $this->loader->getReferences();

        $this->assertSame($res['bob'], $references['bob']);
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
        $user = $res['bob'];

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
        $group = $res['a'];

        $this->assertEquals('group', $group->getName());
    }

    public function testLoadAssignsDataToNonPublicSetters()
    {
        $res = $this->loadData(array(
            self::GROUP => array(
                'a' => array(
                    'sortName' => 'group'
                ),
            ),
        ));
        $group = $res['a'];

        $this->assertEquals('group', $group->getSortName());
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
        $group = $res['a'];

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
        $group = $res['a'];

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

        $this->assertInstanceOf(self::USER, $res['user1']);
        $this->assertInstanceOf(self::USER, $res['user2']);
        $this->assertEquals('alice', $res['user1']->username);
        $this->assertEquals($res['user1']->username, $res['user2']->username);
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

        $this->assertInstanceOf(self::USER, $res['user1']);
        $this->assertInstanceOf(self::USER, $res['user2']);
        $this->assertEquals($res['user1']->getAge(), $res['user2']->favoriteNumber);
    }

    public function testLoadParsesReferencesInFakerProviders()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'bob' => array(
                    'username' => 'Bob',
                ),
                'user' => array(
                    'username' => '<noop(@bob)>',
                ),
            ),
        ));

        $this->assertEquals($res['bob'], $res['user']->username);
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
        $group = $res['a'];

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

    public function testLoadParsesSingleWildcardReferenceWithProperty()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'bob',
                    'email'    => 'bob@gmail.com',
                ),
            ),
            self::GROUP => array(
                'a' => array(
                    'contactEmail' => '@user*->email',
                ),
            ),
        ));
        $group = $res['a'];

        $this->assertEquals('bob@gmail.com', $group->getContactEmail());
    }

    public function testLoadParsesMultiReferencesWithProperty()
    {
        $emails = array_map(function ($char) { return $char.'@gmail.com'; }, range('a', 'z'));
        $data = array();
        foreach ($emails as $key => $email) {
            $data[self::USER]['user'.$key]['email'] = $email;
        }
        $data[self::GROUP]['a']['supportEmails'] = '5x @user*->email';
        $res = $this->loadData($data);

        $group = $this->loader->getReference('a');
        $this->assertCount(5, $group->getSupportEmails());
        foreach ($group->getSupportEmails() as $email) {
            $this->assertContains($email, $emails);
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
        $group = $res['a'];

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

        $this->assertInstanceOf('DateTime', $res['group0']->getCreationDate());
        $this->assertEquals('2012-01-05', $res['group0']->getCreationDate()->format('Y-m-d'));
        $this->assertInstanceOf('DateTime', $res['group1']->getCreationDate());
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

        $this->assertNotEquals('<firstName()>', $res['user0']->username);
        $this->assertNotEmpty($res['user0']->username);
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

        $this->assertNotEquals('<firstName()> <lastName()>', $res['user0']->username);
        $this->assertRegExp('{^[\w\']+ [\w\']+$}i', $res['user0']->username);
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

        $this->assertInstanceOf('DateTime', $res['user0']->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res['user0']->username->getTimestamp());
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

        $this->assertInstanceOf('DateTime', $res['user0']->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res['user0']->username->getTimestamp());
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

        $this->assertInstanceOf('DateTime', $res['user0']->fullname);
        $this->assertGreaterThanOrEqual(strtotime("-20days"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("-9days"), $res['user0']->fullname->getTimestamp());
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

        $this->assertRegExp('{^\d{3} \d{3} \d{3}$}', $res['user0']->username);
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

    public function testSelfReferencingObject()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user{1..10}' => array(
                    'friends' => '3x @user*',
                ),
            ),
        ));

        $this->assertCount(10, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9')->friends[0]);
    }

    public function testSelfReference()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user{1..2}' => array(
                    'username' => 'testuser<current()>',
                    'fullname' => '@self->username',
                ),
            ),
        ));

        $this->assertCount(2, $res);

        $user1 = $this->loader->getReference('user1');
        $this->assertInstanceOf(self::USER, $user1);

        $user2 = $this->loader->getReference('user2');
        $this->assertInstanceOf(self::USER, $user2);

        $this->assertEquals('testuser1', $user1->fullname);
        $this->assertEquals('testuser2', $user2->fullname);
    }

    public function testIdentityProvider()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'testuser',
                    'fullname' => '<identity($username)>',
                ),
                'user2' => array(
                    'username' => 'test_user',
                    'fullname' => '<identity(str_replace("_", " ", $username))>',
                ),
            ),
        ));

        $this->verifyIdentityProviderResults($res);
    }

    public function testDefaultIdentityProviderSugar()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'testuser',
                    'fullname' => '<($username)>',
                ),
                'user2' => array(
                    'username' => 'test_user',
                    'fullname' => '<(str_replace("_", " ", $username))>',
                ),
            ),
        ));

        $this->verifyIdentityProviderResults($res);
    }

    protected function verifyIdentityProviderResults($res)
    {
        $this->assertCount(2, $res);

        $user1 = $this->loader->getReference('user1');
        $this->assertInstanceOf(self::USER, $user1);
        $this->assertEquals('testuser', $user1->fullname);

        $user2 = $this->loader->getReference('user2');
        $this->assertInstanceOf(self::USER, $user2);
        $this->assertEquals('test user', $user2->fullname);
    }

    public function testPassingReferenceToProvider()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'testuser',
                ),
                'user2' => array(
                    'username' => '<(@user1->username)>',
                ),
                'user3' => array(
                    'username' => '<(@user1->username . "_" . @user2->username)>',
                ),
            ),
        ));

        $this->assertCount(3, $res);

        $user1 = $this->loader->getReference('user1');
        $this->assertInstanceOf(self::USER, $user1);

        $user2 = $this->loader->getReference('user2');
        $this->assertInstanceOf(self::USER, $user2);

        $this->assertEquals($user1->username, $user2->username);

        $user3 = $this->loader->getReference('user3');
        $this->assertInstanceOf(self::USER, $user3);

        $this->assertEquals($user1->username . '_' . $user2->username, $user3->username);
    }

    public function testSkippingReferencesInStrings()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user1' => array(
                    'username' => '<("foo@test.com")>',
                ),
                'user2' => array(
                    'username' => '<("foo@test" . "@com")>',
                ),
                'user3' => array(
                    'username' => '<("foo\"@test.com")>',
                ),
            ),
        ));

        $this->assertCount(3, $res);

        $user1 = $this->loader->getReference('user1');
        $this->assertInstanceOf(self::USER, $user1);

        $this->assertEquals('foo@test.com', $user1->username);

        $user2 = $this->loader->getReference('user2');
        $this->assertInstanceOf(self::USER, $user2);

        $this->assertEquals('foo@test@com', $user2->username);

        $user3 = $this->loader->getReference('user3');
        $this->assertInstanceOf(self::USER, $user3);

        $this->assertEquals('foo"@test.com', $user3->username);
    }

    public function testBiDirectionalReferencingObjects()
    {
        $this->loadData(array(
            self::CATEGORY => array(
                'category1' => array(
                    'description' => 'category 1',
                    'lastTopic' => '@topic1',
                ),
            ),
        ), array('enableForwardReferences' => true));

        $this->assertCount(1, $this->loader->getIncompleteInstances());

        $this->loader->load(array(
            self::TOPIC => array(
                'topic1' => array(
                    'subject' => 'topic 1',
                    'parentCategory' => '@category1',
                ),
            ),
        ));

        $this->assertCount(0, $this->loader->getIncompleteInstances());

        $category1 = $this->loader->getReference('category1');
        $topic1 = $this->loader->getReference('topic1');

        $this->assertInstanceOf(self::CATEGORY, $category1);
        $this->assertInstanceOf(self::TOPIC, $category1->lastTopic);
        $this->assertEquals($topic1, $category1->lastTopic);

        $this->assertInstanceOf(self::TOPIC, $topic1);
        $this->assertInstanceOf(self::CATEGORY, $topic1->parentCategory);
        $this->assertEquals($category1, $topic1->parentCategory);
    }

    public function testLoadCreatesEnumsOfObjects()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user_{alice, bob, foo bar}' => array(
                    'username' => '<current()>',
                    'email'    => '<current()>@gmail.com'
                ),
            ),
        ));

        $this->assertCount(3, $res);
        $this->assertInstanceOf(self::USER, $res['user_alice']);
        $this->assertEquals('alice', $res['user_alice']->username);
        $this->assertInstanceOf(self::USER, $res['user_bob']);
        $this->assertEquals('bob', $res['user_bob']->username);
        $this->assertInstanceOf(self::USER, $res['user_foo bar']);
        $this->assertEquals('foo bar', $res['user_foo bar']->username);
    }

    public function testLocalObjectsAreNotReturned()
    {
        $res = $this->loadData(array(
            self::GROUP.' (local)' => array(
                'group' => array(
                    'name' => 'foo'
                ),
            ),
            self::USER => array(
                'user' => array(
                    'email'    => '@group'
                ),
                'user2 (local)' => array(
                    'email'    => '@group'
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertInstanceOf(self::GROUP, $this->loader->getReference('group'));
        $this->assertSame($this->loader->getReference('user')->email, $this->loader->getReference('group'));
        $this->assertSame($this->loader->getReference('user2')->email, $this->loader->getReference('group'));
    }

    public function testTemplateObjectsAreNotReturned()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user (template)' => array(
                    'email'    => 'base@email.com'
                ),
                'user2 (extends user)' => array(
                    'fullname'    => 'testfullname'
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertSame($this->loader->getReference('user2')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user2')->fullname, 'testfullname');
    }

    public function testTemplateCanExtendOtherTemplateObjects()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user (template)' => array(
                    'email'    => 'base@email.com'
                ),
                'user2 (template, extends user)' => array(
                    'favoriteNumber'    => 2
                ),
                'user3 (extends user2)' => array(
                    'fullname'    => 'testfullname'
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user3'));
        $this->assertSame($this->loader->getReference('user3')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user3')->favoriteNumber, 2);
        $this->assertSame($this->loader->getReference('user3')->fullname, 'testfullname');
    }

    public function testMultipleInheritanceInTemplates()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user_minimal (template)' => array(
                    'email'    => 'base@email.com'
                ),
                'user_favorite_number (template)' => array(
                    'fullname' => 'testfullname',
                    'email'    => 'favorite@email.com',
                    'favoriteNumber'    => 2
                ),
                'user_full (template, extends user_minimal, extends user_favorite_number)' => array(
                    'fullname' => 'myfullname',
                ),
                'user (extends user_full)' => array(
                    'friends' => 'myfriends'
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertSame($this->loader->getReference('user')->email, 'favorite@email.com');
        $this->assertSame($this->loader->getReference('user')->favoriteNumber, 2);
        $this->assertSame($this->loader->getReference('user')->fullname, 'myfullname');
        $this->assertSame($this->loader->getReference('user')->friends, 'myfriends');
    }

    public function testMultipleInheritanceInInstance()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user_short_name (template)' => array(
                    'favoriteNumber'    => 2,
                    'username' => 'name'
                ),
                'user_medium_name (template)' => array(
                    'username' => 'name_medium',
                    'fullname' => 'my real name'
                ),
                'user_long_name (template)' => array(
                    'username' => 'my_very_long_name',
                ),
                'user (extends user_short_name, extends user_medium_name, extends user_long_name)' => array(
                    'email' => 'base@email.com',
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertSame($this->loader->getReference('user')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user')->favoriteNumber, 2);
        $this->assertSame($this->loader->getReference('user')->fullname, 'my real name');
        $this->assertSame($this->loader->getReference('user')->username, 'my_very_long_name');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Template user_not_base is not defined
     */
    public function testInheritedObjectDoesntExist()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user_base (template)' => array(
                    'email'    => 'base@email.com'
                ),
                'user (extends user_not_base)' => array(
                    'friends'  => 'myfriends'
                ),
            ),
        ));
    }

    public function testObjectsOverrideTemplates()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user (template)' => array(
                    'email'    => 'base@email.com',
                    'favoriteNumber'    => 2
                ),
                'user2 (extends user)' => array(
                    'favoriteNumber'    => 42
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertSame($this->loader->getReference('user2')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user2')->favoriteNumber, 42);
    }

    public function testObjectsInheritProviders()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user (template)' => array(
                    'fullname'    => '<firstName()>',
                    'favoriteNumber'    => 2
                ),
                'user2 (extends user)' => array(
                    'favoriteNumber'    => 42
                ),
            ),
        ));

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertNotEquals($this->loader->getReference('user2')->fullname, '<firstName()>');
        $this->assertNotEmpty($this->loader->getReference('user2')->fullname);
        $this->assertSame($this->loader->getReference('user2')->favoriteNumber, 42);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Cannot use <current()> out of fixtures ranges
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
     * @expectedExceptionMessage Could not determine how to assign inexistent to a Nelmio\Alice\fixtures\User object
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

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnConstructorsWithRequiredArgs()
    {
        $res = $this->loadData(array(
            self::CONTACT => array(
                'contact' => array(
                    'user' => '@user',
                ),
            ),
        ));
    }

    public function testLoadCanBypassConstructorsWithRequiredArgs()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    'username' => 'alice',
                ),
            ),
            self::CONTACT => array(
                'contact{1..2}' => array(
                    '__construct' => false,
                    'user' => '@user',
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertInstanceOf(self::CONTACT, $this->loader->getReference('contact1'));
        $this->assertSame(
            $this->loader->getReference('user'),
            $this->loader->getReference('contact1')->getUser()
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

    public function testLoadCallsStaticConstructorIfProvided()
    {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('create' => array('alice')),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('alice', $res['user']->username);
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnInvalidStaticConstructor() {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('invalidMethod' => array('alice@example.com')),
                ),
            ),
        ));
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnScalarStaticConstructorArgs() {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('create' => 'alice@example.com'),
                ),
            ),
        ));
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsIfStaticMethodDoesntReturnAnInstance() {
        $res = $this->loadData(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('bogusCreate' => array('alice@example.com')),
                ),
            ),
        ));
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

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('bob', $res['user']->username);
        $this->assertSame('alice@example.com', $res['user']->email);
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

        $this->assertEquals('foo', $res['user0']->username);
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

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('foo', $res['user']->username);
        $this->assertSame('foo@example.com', $res['user']->email);
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

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertInstanceOf('DateTime', $res['user']->birthDate);
    }

    public function testGeneratedValuesAreUnique()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user{0..9}' => array(
                    'username(unique)' => '<randomNumber()>',
                    'favoriteNumber (unique)' => array('<randomNumber()>', '<randomNumber()>'),
                )
            )
        ));

        $usernames = array_map(function (User $u) { return $u->username; }, $res);
        $favNumberPairs = array_map(function (User $u) { return serialize($u->favoriteNumber); }, $res);

        $this->assertEquals($usernames, array_unique($usernames));
        $this->assertEquals($favNumberPairs, array_unique($favNumberPairs));
    }

    public function testGeneratedValuesAreUniqueAcrossAClass()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user{0..4}' => array(
                    'username(unique)' => '<randomNumber()>'
                ),
                'user{5..9}' => array(
                    'username (unique)' => '<randomNumber()>'
                )
            )
        ));

        $usernames = array_map(function (User $u) { return $u->username; }, $res);

        $this->assertEquals($usernames, array_unique($usernames));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUniqueValuesException()
    {
        $loader = new Base("en_US", array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user{0..1}' => array(
                    'username(unique)' => '<fooGenerator()>'
                )
            )
        ));
    }

    public function testCurrentInConstructor()
    {
        $res = $this->loadData(array(
                self::USER => array(
                    'user1' => array(
                        '__construct' => array('alice', 'alice@example.com'),
                    ),
                    'user2' => array(
                        '__construct' => array('bob', 'bob@example.com'),
                    ),
                ),
                self::CONTACT => array(
                    'contact{1..2}' => array(
                        '__construct' => array('@user<current()>'),
                    ),
                ),
            ));

        $this->assertSame(
            $this->loader->getReference('user1'),
            $this->loader->getReference('contact1')->getUser()
        );
        $this->assertSame(
            $this->loader->getReference('user2'),
            $this->loader->getReference('contact2')->getUser()
        );
    }

    public function testCustomSetFunction()
    {
        $loader = $this->createLoader(
            array(
                'providers' => array(new FakerProvider())
            )
        );
        $loader->load(
            array(
                self::USER => array(
                    'user' => array(
                        'username' => 'foo',
                        'fullname' => 'foo bar',
                        '__set' => 'customSetter',
                        'test_variable' => '<noop($username)>',
                    )
                )
            )
        );

        $this->assertEquals('foo set by custom setter', $loader->getReference('user')->username);
        $this->assertEquals('foo bar set by custom setter', $loader->getReference('user')->fullname);
        $this->assertEquals('foo set by custom setter', $loader->getReference('user')->test_variable);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Setter customNonexistantSetter not found in object
     */
    public function testCustomNonexistantSetFunction()
    {
        $this->loadData(
            array(
                self::USER => array(
                    'user' => array(
                        'username' => 'foo',
                        'fullname' => 'foo bar',
                        '__set' => 'customNonexistantSetter'
                    )
                )
            )
        );
    }

    public function testNullVariable()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $loader->load(array(
            self::USER => array(
                'user' => array(
                    'username' => '0%? adrien',
                    'fullname' => '<noop($username)>',
                ),
            ),
        ));

        $this->assertNull($loader->getReference('user')->username);
        $this->assertNull($loader->getReference('user')->fullname);
    }

    public function testAtLiteral()
    {
        $loader = new Base('en_US', array(new FakerProvider));
        $res = $loader->load(array(
            self::USER => array(
                'user' => array(
                    '__construct' => array('\\@<fooGenerator()> \\\\@foo \\\\\\@foo \\foo'),
                ),
            ),
        ));

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('@foo \\@foo \\@foo \\foo', $res['user']->username);
    }
}

class FakerProvider
{
    public function fooGenerator()
    {
        return 'foo';
    }

    public function randomNumber()
    {
        return mt_rand(0, 9);
    }

    public function noop($str)
    {
        return $str;
    }
}
