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

use Nelmio\Alice\support\extensions;
use Nelmio\Alice\support\extensions\FakerProviderWithRequiredParameter;
use Nelmio\Alice\support\models\DummyWithVariadicConstructor;
use Nelmio\Alice\support\models\Group;
use Nelmio\Alice\support\models\MagicUser;
use Nelmio\Alice\support\models\typehint\Dummy;
use Nelmio\Alice\support\models\typehint\DummyWithInterface;
use Nelmio\Alice\support\models\typehint\RelatedDummy;
use Nelmio\Alice\support\models\User;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const MAGIC_USER = 'Nelmio\Alice\support\models\MagicUser';
    const STATIC_USER = 'Nelmio\Alice\support\models\StaticUser';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const CONTACT = 'Nelmio\Alice\support\models\Contact';
    const PRIVATE_CONSTRUCTOR_CLASS = 'Nelmio\Alice\support\models\PrivateConstructorClass';
    const NAMED_CONSTRUCTOR_CLASS = 'Nelmio\Alice\support\models\NamedConstructorClass';

    /**
     * @var \Nelmio\Alice\Fixtures\Loader
     */
    protected $loader;

    protected function loadData(array $data, array $options = [])
    {
        $loader = $this->createLoader($options);

        return $loader->load($data);
    }

    protected function createLoader(array $options = [])
    {
        $defaults = [
            'locale' => 'en_US',
            'providers' => [],
            'seed' => 1,
            'parameters' => [],
        ];
        $options = array_merge($defaults, $options);

        return $this->loader = new Loader(
            $options['locale'],
            $options['providers'],
            $options['seed'],
            $options['parameters']
        );
    }

    public function testLoadCreatesInstances()
    {
        $objects = $this->loadData([
            self::USER => [
                'alice-user' => [],
                'bob' => [],
            ],
        ]);

        $this->assertCount(2, $objects);

        $alice = $objects['alice-user'];
        $bob = $objects['bob'];

        $this->assertInstanceOf(User::class, $alice);
        $this->assertInstanceOf(User::class, $bob);
    }

    public function testGetReference()
    {
        $objects = $this->loadData([
            self::USER => [
                'alice-user' => [],
                'bob' => [],
            ],
        ]);

        $this->assertCount(2, $objects);

        $this->assertSame($this->loader->getReference('alice-user'), $objects['alice-user']);
        $this->assertSame($this->loader->getReference('bob'), $objects['bob']);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Instance foo is not defined
     */
    public function testGetBadReference()
    {
        $this->loadData([]);
        $this->loader->getReference('foo');
    }

    public function testLoadUnparsableFile()
    {
        $file = __DIR__.'/../support/fixtures/not-parsable';
        $this->expectException(
            '\UnexpectedValueException',
            sprintf('%s cannot be parsed - no parser exists that can handle it.', $file)
        );
        $this->createLoader()->load($file);
    }

    public function testFakerProviderWithEmptyValues()
    {
        $objects = $this
            ->createLoader([
                'providers' => [
                    new FakerProviderWithRequiredParameter(),
                ]
            ])
            ->load([
                DummyWithVariadicConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            '<passValue([])>',
                            '<passValue(0)>',
                            '<passValue("")>',
                            '<passValue(null)>',
                            '<passValue(false)>',
                        ],
                    ],
                ],
            ])
        ;

        $this->assertCount(1, $objects);
        /** @var DummyWithVariadicConstructor $dummy */
        $dummy = $objects['dummy'];

        $this->assertSame(
            [
                [],
                0,
                '',
                null,
                false,
            ],
            $dummy->data
        );
    }

    /**
     * @group legacy
     */
    public function testCreatePrivateConstructorInstance()
    {
        $loader = new Loader('en_US', [new FakerProvider]);

        $res = $loader->load($file = __DIR__.'/../support/fixtures/private_constructs.yml');
        $this->assertInstanceOf(self::PRIVATE_CONSTRUCTOR_CLASS, $res['test1']);
    }

    public function testCreateNamedConstructorInstance()
    {
        $res = $this->loadData([
            self::NAMED_CONSTRUCTOR_CLASS => [
                'foo' => [
                    '__construct' => ['withLambda' => ['λ']],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::NAMED_CONSTRUCTOR_CLASS, $res['foo']);
        $this->assertSame('λ', $res['foo']->lambda);
    }

    public function testLoadInvalidFile()
    {
        $file = __DIR__.'/../support/fixtures/invalid.php';
        $this->expectException(
            '\UnexpectedValueException',
            sprintf('Included file "%s" must return an array of data', $file)
        );
        $this->createLoader()->load($file);
    }

    public function testLoadEmptyFile()
    {
        $res = $this->createLoader()->load($file = __DIR__.'/../support/fixtures/empty.php');
        $this->assertSame([], $res);
    }

    public function testLoadSequencedItems()
    {
        $object = $this->createLoader()->load($file = __DIR__.'/../support/fixtures/sequenced_items.yml');

        $this->assertArrayHasKey('group1', $object);
        $this->assertInstanceOf(self::GROUP, $object['group1']);
        $counter = 1;
        foreach ($object['group1']->getMembers() as $member) {
            $this->assertEquals($member->uuid, $counter);
            $counter++;
        }
    }

    public function testGetReferences()
    {
        $res = $this->loadData([
            self::USER => [
                'bob' => [],
            ],
        ]);
        $references = $this->loader->getReferences();

        $this->assertSame($res['bob'], $references['bob']);
    }

    public function testSetReferencesClearsAndSetsReferences()
    {
        $res = $this->loadData([
            self::USER => [
                'bob' => [],
                'jim' => [],
            ],
        ]);

        $this->loader->setReferences(['bob' => new User]);
        $references = $this->loader->getReferences();

        $this->assertNotSame($res['bob'], $references['bob']);
        $this->assertNotContains($res['jim'], $references);
        $this->assertCount(1, $references);
    }

    public function testLoadAssignsDataToProperties()
    {
        $res = $this->loadData([
            self::USER => [
                'bob' => [
                    'username' => 'bob',
                ],
            ],
        ]);
        $user = $res['bob'];

        $this->assertEquals('bob', $user->username);
    }

    public function testLoadAssignsDataToSetters()
    {
        $res = $this->loadData([
            self::GROUP => [
                'a' => [
                    'name' => 'group',
                ],
            ],
        ]);
        $group = $res['a'];

        $this->assertEquals('group', $group->getName());
    }

    /**
     * @group legacy
     */
    public function testLoadAssignsDataToNonPublicSetters()
    {
        $res = $this->loadData([
            self::GROUP => [
                'a' => [
                    'sortName' => 'group',
                ],
            ],
        ]);
        $group = $res['a'];

        $this->assertEquals('group', $group->getSortName());
    }

    public function testSnakeCaseProperty()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'familyName' => 'Wonderland',
                    'display_name' => 'Hatter',
                ],
            ],
        ]);
        /** @var User $user */
        $user = $res['user0'];

        $this->assertEquals('Wonderland', $user->family_name);
        $this->assertEquals('Mad Hatter', $user->display_name);
    }

    public function testLoadAssignsDataToMagicCall()
    {
        $res = $this->loadData([
            self::MAGIC_USER => [
                'a' => [
                    'username' => 'bob',
                ],
            ],
        ]);
        /** @var MagicUser $user */
        $user = $res['a'];

        $this->assertInstanceOf(self::MAGIC_USER, $user);
        $this->assertEquals('bob set by __call', $user->getUsername());
    }

    public function testLoadAddsReferencesToAdders()
    {
        $res = $this->loadData([
            self::GROUP => [
                'a' => [
                    'members' => [$user = new User()],
                ],
            ],
        ]);
        /** @var Group $group */
        $group = $res['a'];

        $this->assertInstanceOf(self::GROUP, $group);
        $this->assertSame($user, current($group->getMembers()));
    }

    public function testLoadParsesReferences()
    {
        $objects = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'alice',
                ],
                'user-2' => [
                    'username' => 'bob',
                ],
            ],
            self::GROUP => [
                'a' => [
                    'members' => [
                        '@user1',
                        '@user-2',
                    ]
                ],
            ],
        ]);
        /** @var Group $group */
        $group = $objects['a'];

        $this->assertInstanceOf(Group::class, $group);

        $members = $group->getMembers();

        $this->assertCount(2, $members);
        foreach ($members as $member) {
            $this->assertInstanceOf(User::class, $member);
        }

        $this->assertEquals('alice', $members[0]->username);
        $this->assertEquals('bob', $members[1]->username);
    }

    /**
     * @group legacy
     */
    public function testLoadParsesReferencesInQuotes()
    {
        $result = $this->loadData([
            User::class => [
                'user1' => [
                    'username' => 'alice',
                ],
            ],
            Group::class => [
                'group' => [
                    'members' => ['\'@user1\'']
                ],
            ],
        ]);
        /** @var Group $group */
        $group = $result['group'];

        $this->assertInstanceOf(User::class, current($group->getMembers()));
        $this->assertEquals('alice', current($group->getMembers())->username);
    }

    public function testLoadParsesPropertyReferences()
    {
        $objects = $this->loadData([
            self::USER => [
                'user-1' => [
                    'username' => 'alice',
                ],
                'user2' => [
                    'username' => '@user-1->username',
                ],
            ]
        ]);

        $user1 = $objects['user-1'];
        $user2 = $objects['user2'];

        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(User::class, $user2);
        $this->assertEquals('alice', $user1->username);
        $this->assertEquals($user1->username, $user2->username);
    }

    public function testLoadParsesPropertyReferencesGetter()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'alice',
                ],
                'user2' => [
                    'favoriteNumber' => '@user1->age',
                ],
            ]
        ]);

        $this->assertInstanceOf(self::USER, $res['user1']);
        $this->assertInstanceOf(self::USER, $res['user2']);
        $this->assertEquals($res['user1']->getAge(), $res['user2']->favoriteNumber);
    }

    public function testLoadParsesReferencesInFakerProviders()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'bob' => [
                    'username' => 'Bob',
                ],
                'user' => [
                    'username' => '<noop(@bob)>',
                ],
            ],
        ]);

        $this->assertEquals($res['bob'], $res['user']->username);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Property doesnotexist is not defined for instance user1
     */
    public function testLoadParsesPropertyReferencesDoesNotExist()
    {
        $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'alice',
                ],
                'user2' => [
                    'username' => '@user1->doesnotexist',
                ],
            ]
        ]);
    }

    public function testLoadParsesSingleWildcardReference()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'bob',
                ],
            ],
            self::GROUP => [
                'a' => [
                    'owner' => '@user*',
                ],
            ],
        ]);
        $group = $res['a'];

        $this->assertInstanceOf(self::USER, $group->getOwner());
        $this->assertEquals('bob', $group->getOwner()->username);
    }

    public function testLoadParsesMultiReferences()
    {
        $usernames = range('a', 'z');
        $data = [];
        foreach ($usernames as $key => $username) {
            $data[self::USER]['user'.$key]['username'] = $username;
        }
        $data[self::GROUP]['a']['members'] = '5x @user*';
        $this->loadData($data);

        $group = $this->loader->getReference('a');
        $this->assertCount(5, $group->getMembers());
        foreach ($group->getMembers() as $member) {
            $this->assertContains($member->username, $usernames);
        }
    }

    public function testLoadParsesZeroReferences()
    {
        $usernames = range('a', 'z');
        $data = [];
        foreach ($usernames as $key => $username) {
            $data[self::USER]['user'.$key]['username'] = $username;
        }
        $data[self::GROUP]['a']['members'] = '0x @user*';
        $this->loadData($data);

        $group = $this->loader->getReference('a');
        $this->assertCount(0, $group->getMembers());
    }

    public function testLoadParsesSingleWildcardReferenceWithProperty()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'bob',
                    'email' => 'bob@gmail.com',
                ],
            ],
            self::GROUP => [
                'a' => [
                    'contactEmail' => '@user*->email',
                ],
            ],
        ]);
        $group = $res['a'];

        $this->assertEquals('bob@gmail.com', $group->getContactEmail());
    }

    public function testLoadParsesMultiReferencesWithProperty()
    {
        $emails = array_map(function ($char) { return $char.'@gmail.com'; }, range('a', 'z'));
        $data = [];
        foreach ($emails as $key => $email) {
            $data[self::USER]['user'.$key]['email'] = $email;
        }
        $data[self::GROUP]['a']['supportEmails'] = '5x @user*->email';
        $this->loadData($data);

        $group = $this->loader->getReference('a');
        $this->assertCount(5, $group->getSupportEmails());
        foreach ($group->getSupportEmails() as $email) {
            $this->assertContains($email, $emails);
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Instance mask "user*" did not match any existing instance, make sure the object is
     *                           created after its references
     */
    public function testLoadFailsMultiReferencesIfNoneMatch()
    {
        $data = [
            self::GROUP => [
                'a' => [
                    'members' => '5x @user*',
                ],
            ],
        ];
        $this->loadData($data);
    }

    public function testLoadParsesMultiReferencesAndOnlyPicksUniques()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'alice',
                ],
            ],
            self::GROUP => [
                'a' => [
                    'members' => '5x @user*',
                ],
            ],
        ]);
        $group = $res['a'];

        $this->assertCount(1, $group->getMembers());
    }

    public function testLoadObjectsWithDotsInTheirReferences()
    {
        $res = $this->loadData([
            self::USER => [
                'user.alice' => [
                    'username' => 'alice',
                ],
                'user.alias.alice_alias' => [
                    'username' => '@user.alice->username',
                ],
                'user.deep_alias' => [
                    'username' => '@user.alias.alice_alias->username',
                ],
            ]
        ]);

        $this->assertCount(3, $res);

        $this->assertInstanceOf(self::USER, $res['user.alice']);
        $this->assertInstanceOf(self::USER, $res['user.alias.alice_alias']);
        $this->assertInstanceOf(self::USER, $res['user.deep_alias']);

        $this->assertEquals('alice', $res['user.alice']->username);
        $this->assertEquals($res['user.alice']->username, $res['user.alias.alice_alias']->username);
        $this->assertEquals($res['user.alias.alice_alias']->username, $res['user.deep_alias']->username);
    }
    /**
     * @dataProvider provideSpecialCharactersData
     */
    public function testLoadObjectsWithSpecialCharactersInTheirReferences($data, $keys)
    {
        $res = $this->loadData($data);

        $this->assertCount(3, $res);
        foreach ($keys as $key) {
            $this->assertInstanceOf(self::USER, $res[$key]);
        }

        $this->assertEquals('alice', $res[$keys[0]]->username);
        $this->assertEquals($res[$keys[0]]->username, $res[$keys[1]]->username);
        $this->assertEquals($res[$keys[1]]->username, $res[$keys[2]]->username);
    }

    public function testLoadParsesOptionalValuesWithPercents()
    {
        $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '50%? name',
                ],
                'user1' => [
                    'username' => '50%? name : nothing',
                ],
            ],
        ]);

        $this->assertContains($this->loader->getReference('user0')->username, ['name', null]);
        $this->assertContains($this->loader->getReference('user1')->username, ['name', 'nothing']);
    }

    /**
     * @group legacy
     */
    public function testLoadParsesOptionalValuesWithPercentsLimits()
    {
        $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '0%? name',
                ],
                'user1' => [
                    'username' => '100%? hello',
                ],
            ],
        ]);

        $this->assertEquals($this->loader->getReference('user0')->username, null);
        $this->assertEquals($this->loader->getReference('user1')->username, 'hello');
    }

    /**
     * @group legacy
     */
    public function testLoadParsesOptionalValuesWithFloats()
    {
        $this->loadData([
            User::class => [
                'user0' => [
                    'username' => '0.5? name',
                ],
                'user1' => [
                    'username' => '0.5? name : nothing',
                ],
                'user2' => [
                    'username' => '0? name : nothing',
                ],
                'user3' => [
                    'username' => '1? name : nothing',
                ],
            ],
        ]);

        $this->assertContains($this->loader->getReference('user0')->username, ['name', null]);
        $this->assertContains($this->loader->getReference('user1')->username, ['name', 'nothing']);
        $this->assertEquals('nothing', $this->loader->getReference('user2')->username);
        $this->assertEquals('name', $this->loader->getReference('user3')->username);
    }

    /**
     * @group legacy
     */
    public function testLoadCoercesDatesForDateTimeHints()
    {
        $result = $this->loadData([
            Group::class => [
                'group0' => [
                    'creationDate' => '2012-01-05',
                ],
                'group1' => [
                    'creationDate' => '<unixTime()>',
                ],
            ],
        ]);

        /** @var Group $group0 */
        $group0 = $result['group0'];
        /** @var Group $group1 */
        $group1 = $result['group1'];

        $this->assertInstanceOf(\DateTime::class, $group0->getCreationDate());
        $this->assertEquals('2012-01-05', $group0->getCreationDate()->format('Y-m-d'));

        $this->assertInstanceOf(\DateTime::class, $group1->getCreationDate());
    }

    public function testCreatesDateTimeWithIdentity()
    {
        $result = $this->loadData([
            Group::class => [
                'group' => [
                    'name' => '<(new \DateTime("2012-01-05"))>',
                ],
            ],
        ]);

        /** @var Group $group */
        $group = $result['group'];

        $this->assertInstanceOf(\DateTime::class, $group->getName());
        $this->assertEquals('2012-01-05', $group->getName()->format('Y-m-d'));
    }

    public function testLoadParsesFakerData()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<firstName()>',
                ],
            ],
        ]);

        $this->assertNotEquals('<firstName()>', $res['user0']->username);
        $this->assertNotEmpty($res['user0']->username);
    }

    public function testLoadParsesFakerDataMultiple()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<firstName()> <lastName()>',
                ],
            ],
        ]);

        $this->assertNotEquals('<firstName()> <lastName()>', $res['user0']->username);
        $this->assertRegExp('{^[\w\']+ [\w\']+$}i', $res['user0']->username);
    }

    public function testLoadParsesFakerDataWithArgs()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<dateTimeBetween("yesterday", "tomorrow")>',
                ],
            ],
        ]);

        $this->assertInstanceOf('DateTime', $res['user0']->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res['user0']->username->getTimestamp());
    }

    public function testLoadParsesFakerDataWithPhpArgs()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<dateTimeBetween("yest"."erday", strrev("omot")."rrow")>',
                ],
            ],
        ]);

        $this->assertInstanceOf('DateTime', $res['user0']->username);
        $this->assertGreaterThanOrEqual(strtotime("yesterday"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("tomorrow"), $res['user0']->username->getTimestamp());
    }

    public function testLoadParsesVariables()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<dateTimeBetween("-20days", "-10days")>',
                    'fullname' => '<dateTimeBetween($username, "-9days")>',
                ],
            ],
        ]);

        $this->assertInstanceOf('DateTime', $res['user0']->fullname);
        $this->assertGreaterThanOrEqual(strtotime("-20days"), $res['user0']->username->getTimestamp());
        $this->assertLessThanOrEqual(strtotime("-9days"), $res['user0']->fullname->getTimestamp());
    }

    public function testLoadParsesFakerDataWithLocale()
    {
        $res = $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<fr_FR:siren()>',
                ],
            ],
        ]);

        $this->assertRegExp('{^\d{3} \d{3} \d{3}$}', $res['user0']->username);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown formatter "siren"
     */
    public function testLoadParsesFakerDataUsesDefaultLocale()
    {
        $this->loadData([
            self::USER => [
                'user0' => [
                    'username' => '<siren()>',
                ],
            ],
        ]);
    }

    public function testLoadCreatesInclusiveRangesOfObjects()
    {
        $res = $this->loadData([
            self::USER => [
                'user{0..10}' => [
                    'username' => 'alice',
                ],
            ],
        ]);

        $this->assertCount(11, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user0'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user10'));
    }

    public function testLoadCreatesExclusiveRangesOfObjects()
    {
        $res = $this->loadData([
            self::USER => [
                'user{0..9}' => [
                    'username' => 'alice',
                ],
            ],
        ]);

        $this->assertCount(10, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user0'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9'));
    }

    public function testLoadSwapsRanges()
    {
        $res = $this->loadData([
            self::USER => [
                'user{10..9}' => [
                    'username' => 'alice',
                ],
            ],
        ]);

        $this->assertCount(2, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9'));
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user10'));
    }

    public function testSelfReferencingObject()
    {
        $res = $this->loadData([
            self::USER => [
                'user{1..10}' => [
                    'friends' => '3x @user*',
                ],
            ],
        ]);

        $this->assertCount(10, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user9')->friends[0]);
    }

    public function testSelfReference()
    {
        $res = $this->loadData([
            self::USER => [
                'user{1..2}' => [
                    'username' => 'testuser<current()>',
                    'fullname' => '@self->username',
                ],
            ],
        ]);

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
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'testuser',
                    'fullname' => '<identity($username)>',
                ],
                'user2' => [
                    'username' => 'test_user',
                    'fullname' => '<identity(str_replace("_", " ", $username))>',
                ],
            ],
        ]);

        $this->verifyIdentityProviderResults($res);
    }

    public function testDefaultIdentityProviderSugar()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'testuser',
                    'fullname' => '<($username)>',
                ],
                'user2' => [
                    'username' => 'test_user',
                    'fullname' => '<(str_replace("_", " ", $username))>',
                ],
            ],
        ]);

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
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => 'testuser',
                ],
                'user2' => [
                    'username' => '<(@user1->username)>',
                ],
                'user3' => [
                    'username' => '<(@user1->username . "_" . @user2->username)>',
                ],
            ],
        ]);

        $this->assertCount(3, $res);

        $user1 = $this->loader->getReference('user1');
        $this->assertInstanceOf(self::USER, $user1);

        $user2 = $this->loader->getReference('user2');
        $this->assertInstanceOf(self::USER, $user2);

        $this->assertEquals($user1->username, $user2->username);

        $user3 = $this->loader->getReference('user3');
        $this->assertInstanceOf(self::USER, $user3);

        $this->assertEquals($user1->username.'_'.$user2->username, $user3->username);
    }

    public function testSkippingReferencesInStrings()
    {
        $res = $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => '<("foo@test.com")>',
                ],
                'user2' => [
                    'username' => '<("foo@test" . "@com")>',
                ],
                'user3' => [
                    'username' => '<("foo\"@test.com")>',
                ],
            ],
        ]);

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

    public function testLoadCreatesEnumsOfObjects()
    {
        $res = $this->loadData([
            self::USER => [
                'user_{alice, bob, foo, bar}' => [
                    'username' => '<current()>',
                    'email' => '<current()>@gmail.com',
                ],
            ],
        ]);

        $this->assertCount(4, $res);

        $this->assertInstanceOf(self::USER, $res['user_alice']);
        $this->assertEquals('alice', $res['user_alice']->username);

        $this->assertInstanceOf(self::USER, $res['user_bob']);
        $this->assertEquals('bob', $res['user_bob']->username);

        $this->assertInstanceOf(self::USER, $res['user_foo']);
        $this->assertEquals('foo', $res['user_foo']->username);

        $this->assertInstanceOf(self::USER, $res['user_bar']);
        $this->assertEquals('bar', $res['user_bar']->username);
    }

    /**
     * @group legacy
     */
    public function testLoadCreatesEnumsOfObjectsWithMalformedList()
    {
        $res = $this->loadData([
            self::USER => [
                'user_{alice, bob, foo bar}' => [
                    'username' => '<current()>',
                    'email' => '<current()>@gmail.com',
                ],
            ],
        ]);

        $this->assertCount(3, $res);

        $this->assertInstanceOf(self::USER, $res['user_alice']);
        $this->assertEquals('alice', $res['user_alice']->username);

        $this->assertInstanceOf(self::USER, $res['user_bob']);
        $this->assertEquals('bob', $res['user_bob']->username);

        $this->assertInstanceOf(self::USER, $res['user_foo bar']);
        $this->assertEquals('foo bar', $res['user_foo bar']->username);
    }

    /**
     * @group legacy
     */
    public function testLocalObjectsAreNotReturned()
    {
        $result = $this->loadData([
            sprintf('%s (local)', Group::class) => [
                'foo_group' => [
                    'name' => 'foo',
                ],
            ],
            User::class => [
                'user1' => [
                    'email' => '@foo_group',
                ],
                'user2 (local)' => [
                    'email' => '@foo_group',
                ],
            ],
        ]);

        // The loader has still the reference of each fixtures
        $user1 = $this->loader->getReference('user1');
        $user2 = $this->loader->getReference('user2');
        $group = $this->loader->getReference('foo_group');
        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(User::class, $user2);
        $this->assertInstanceOf(Group::class, $group);
        $this->assertSame($user1->email, $group);
        $this->assertSame($user2->email, $group);

        $this->assertCount(1, $result);
        $this->assertSame(
            [
                'user1' => $user1,
            ],
            $result
        );
    }

    public function testTemplateObjectsAreNotReturned()
    {
        $res = $this->loadData([
            self::USER => [
                'user (template)' => [
                    'email' => 'base@email.com',
                ],
                'user2 (extends user)' => [
                    'fullname' => 'testfullname',
                ],
            ],
        ]);

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertSame($this->loader->getReference('user2')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user2')->fullname, 'testfullname');
    }

    public function testTemplatesAreKeptBetweenFiles()
    {
        $objects = $this->createLoader()->load(__DIR__.'/Files/includes/user.yml');

        $this->assertCount(1, $objects);
        /** @var User $user0 */
        $user0 = $this->loader->getReference('user0');
        $this->assertInstanceOf(self::USER, $user0);
        $this->assertSame($user0->username, 'Base user');
    }

    public function testTemplateCanExtendOtherTemplateObjectsCombinedWithRange()
    {
        $res = $this->loadData([
            self::USER => [
                'us{er, rr} (template)' => [
                    'email' => 'base@email.com'
                ],
                'user{1..2} (template, extends user)' => [
                    'favoriteNumber' => 2
                ],
                '{user, uzer}3 (extends user2)' => [
                    'fullname' => 'testfullname'
                ],
            ],
        ]);

        $this->assertCount(2, $res);
        foreach (['user3', 'uzer3'] as $key) {
            $this->assertInstanceOf(self::USER, $this->loader->getReference($key));

            $this->assertSame($this->loader->getReference($key)->email, 'base@email.com');
            $this->assertSame($this->loader->getReference($key)->favoriteNumber, 2);
            $this->assertSame($this->loader->getReference($key)->fullname, 'testfullname');
        }
    }

    /**
     * @group legacy
     */
    public function testTemplateCanExtendOtherTemplateObjectsCombinedWithRangeWithLegacySyntax()
    {
        $res = $this->loadData([
            self::USER => [
                'us{er,rr} (template)' => [
                    'email' => 'base@email.com'
                ],
                'user{1..2} (template, extends user)' => [
                    'favoriteNumber' => 2
                ],
                '{user,uzer}3 (extends user2)' => [
                    'fullname' => 'testfullname'
                ],
            ],
        ]);

        $this->assertCount(2, $res);
        foreach (['user3', 'uzer3'] as $key) {
            $this->assertInstanceOf(self::USER, $this->loader->getReference($key));

            $this->assertSame($this->loader->getReference($key)->email, 'base@email.com');
            $this->assertSame($this->loader->getReference($key)->favoriteNumber, 2);
            $this->assertSame($this->loader->getReference($key)->fullname, 'testfullname');
        }
    }

    public function testMultipleInheritanceInTemplates()
    {
        $res = $this->loadData([
            self::USER => [
                'user_minimal (template)' => [
                    'email' => 'base@email.com',
                ],
                'user_favorite_number (template)' => [
                    'fullname' => 'testfullname',
                    'email' => 'favorite@email.com',
                    'favoriteNumber' => 2,
                ],
                'user_full (template, extends user_minimal, extends user_favorite_number)' => [
                    'fullname' => 'myfullname',
                    'friends' => 'testfriends',
                ],
                'user (extends user_full)' => [
                    'friends' => 'myfriends',
                ],
            ],
        ]);

        $this->assertCount(1, $res);
        /** @var User $user */
        $user = $this->loader->getReference('user');
        $this->assertInstanceOf(self::USER, $user);
        $this->assertSame('favorite@email.com', $user->email);
        $this->assertSame(2, $user->favoriteNumber);
        $this->assertSame('myfullname', $user->fullname);
        $this->assertSame('myfriends', $user->friends);
    }

    public function testMultipleInheritanceInInstance()
    {
        $res = $this->loadData([
            self::USER => [
                'user_short_name (template)' => [
                    'favoriteNumber' => 2,
                    'username' => 'name',
                ],
                'user_medium_name (template)' => [
                    'username' => 'name_medium',
                    'fullname' => 'my real name',
                ],
                'user_long_name (template)' => [
                    'username' => 'my_very_long_name',
                    'email' => 'test@email.com',
                ],
                'user (extends user_short_name, extends user_medium_name, extends user_long_name)' => [
                    'email' => 'base@email.com',
                ],
            ],
        ]);

        $this->assertCount(1, $res);
        /** @var User $user */
        $user = $this->loader->getReference('user');
        $this->assertInstanceOf(self::USER, $user);
        $this->assertSame('base@email.com', $user->email);
        $this->assertSame(2, $user->favoriteNumber);
        $this->assertSame('my real name', $user->fullname);
        $this->assertSame('my_very_long_name', $user->username);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Template user_not_base is not defined
     */
    public function testInheritedObjectDoesntExist()
    {
        $this->loadData([
            self::USER => [
                'user_base (template)' => [
                    'email' => 'base@email.com',
                ],
                'user (extends user_not_base)' => [
                    'friends' => 'myfriends',
                ],
            ],
        ]);
    }

    public function testObjectsOverrideTemplates()
    {
        $res = $this->loadData([
            self::USER => [
                'user (template)' => [
                    'email' => 'base@email.com',
                    'favoriteNumber' => 2,
                ],
                'user2 (extends user)' => [
                    'favoriteNumber' => 42,
                ],
            ],
        ]);

        $this->assertCount(1, $res);
        $this->assertInstanceOf(self::USER, $this->loader->getReference('user2'));
        $this->assertSame($this->loader->getReference('user2')->email, 'base@email.com');
        $this->assertSame($this->loader->getReference('user2')->favoriteNumber, 42);
    }

    public function testObjectsInheritProviders()
    {
        $res = $this->loadData([
            self::USER => [
                'user (template)' => [
                    'fullname' => '<firstName()>',
                    'favoriteNumber' => 2,
                ],
                'user2 (extends user)' => [
                    'favoriteNumber' => 42,
                ],
            ],
        ]);

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
        $this->loadData([
            self::USER => [
                'user1' => [
                    'username' => '<current()>',
                ],
            ],
        ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Could not determine how to assign inexistent to a Nelmio\Alice\support\models\User
     *                           object
     */
    public function testArbitraryPropertyNamesFail()
    {
        $this->loadData([
            self::USER => [
                'user1' => [
                    'inexistent' => 'foo',
                ],
            ],
        ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnConstructorsWithRequiredArgs()
    {
        $this->loadData([
            self::CONTACT => [
                'contact' => [
                    'user' => '@user',
                ],
            ],
        ]);
    }

    /**
     * @group legacy
     */
    public function testLoadCanBypassConstructorsWithRequiredArgs()
    {
        $this->loadData([
            self::USER => [
                'user' => [
                    'username' => 'alice',
                ],
            ],
            self::CONTACT => [
                'contact{1..2}' => [
                    '__construct' => false,
                    'user' => '@user',
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertInstanceOf(self::CONTACT, $this->loader->getReference('contact1'));
        $this->assertSame(
            $this->loader->getReference('user'),
            $this->loader->getReference('contact1')->getUser()
        );
    }

    public function testLoadCallsConstructorIfProvided()
    {
        $this->loadData([
            self::USER => [
                'user' => [
                    '__construct' => ['alice', 'alice@example.com'],
                ],
            ],
            self::CONTACT => [
                'contact' => [
                    '__construct' => ['@user'],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $this->loader->getReference('user'));
        $this->assertSame('alice', $this->loader->getReference('user')->username);
        $this->assertSame('alice@example.com', $this->loader->getReference('user')->email);
        $this->assertSame(
            $this->loader->getReference('user'),
            $this->loader->getReference('contact')->getUser()
        );
    }

    public function testLoadCallsConstructorByDefault()
    {
        $res = $this->loadData([
            self::USER => [
                'user' => []
            ]
        ]);

        $this->assertSame('tmp-username', $res['user']->username);
    }

    public function testLoadCallsStaticConstructorIfProvided()
    {
        $res = $this->loadData([
            self::STATIC_USER => [
                'user' => [
                    '__construct' => ['create' => ['alice@example.com']],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::STATIC_USER, $res['user']);
        $this->assertSame('alice', $res['user']->username);
        $this->assertSame('alice@example.com', $res['user']->email);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnInvalidStaticConstructor()
    {
        $this->loadData([
            self::USER => [
                'user' => [
                    '__construct' => ['invalidMethod' => ['alice@example.com']],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsOnScalarStaticConstructorArgs()
    {
        $this->loadData([
            self::USER => [
                'user' => [
                    '__construct' => ['create' => 'alice@example.com'],
                ],
            ],
        ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage
     */
    public function testLoadFailsIfStaticMethodDoesntReturnAnInstance()
    {
        $this->loadData([
            self::STATIC_USER => [
                'user' => [
                    '__construct' => ['bogusCreate' => ['alice', 'alice@example.com']],
                ],
            ],
        ]);
    }

    /**
     * @group legacy
     */
    public function testLoadCallsCustomMethodAfterCtor()
    {
        $res = $this->loadData([
            self::USER => [
                'user' => [
                    'doStuff' => [0, 3, 'bob'],
                    '__construct' => ['alice', 'alice@example.com'],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('bob', $res['user']->username);
        $this->assertSame('alice@example.com', $res['user']->email);
    }

    public function testConstructorCustomProviders()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'user0' => [
                    'username' => '<fooGenerator()>',
                ],
            ],
        ]);

        $this->assertEquals('foo', $res['user0']->username);
    }

    public function testLoadCallsCustomMethodWithMultipleArgumentsAndCustomProviders()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'user' => [
                    '__construct' => ['<fooGenerator()>', '<fooGenerator()>@example.com'],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame('foo', $res['user']->username);
        $this->assertSame('foo@example.com', $res['user']->email);
    }

    public function testLoadCallsConstructorWithHintedParams()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'user' => [
                    '__construct' => [null, null, '<dateTimeBetween("-10years", "now")>'],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertInstanceOf('DateTime', $res['user']->birthDate);
    }

    /**
     * @group legacy
     */
    public function testGeneratedValuesAreUnique()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'user{0..9}' => [
                    'username(unique)' => '<numberBetween()>',
                    'favoriteNumber (unique)' => ['<numberBetween()>', '<numberBetween()>'],
                ]
            ]
        ]);

        $usernames = array_map(function (User $user) { return $user->username; }, $res);
        $favNumberPairs = array_map(function (User $user) { return serialize($user->favoriteNumber); }, $res);

        $this->assertEquals($usernames, array_unique($usernames));
        $this->assertEquals($favNumberPairs, array_unique($favNumberPairs));
    }

    public function testGeneratedValuesAreUniqueAcrossAClass()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'user{0..4}' => [
                    'username(unique)' => '<numberBetween()>',
                ],
                'user{5..9}' => [
                    'username (unique)' => '<numberBetween()>',
                ]
            ]
        ]);
        $usernames = array_map(function (User $u) { return $u->username; }, $res);

        $this->assertEquals($usernames, array_unique($usernames));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUniqueValuesException()
    {
        $loader = new Loader("en_US", [new FakerProvider]);
        $loader->load([
            self::USER => [
                'user{0..1}' => [
                    'username(unique)' => '<fooGenerator()>',
                ]
            ]
        ]);
    }

    public function testCurrentInConstructor()
    {
        $this->loadData([
            self::USER => [
                'user1' => [
                    '__construct' => ['alice', 'alice@example.com'],
                ],
                'user2' => [
                    '__construct' => ['bob', 'bob@example.com'],
                ],
            ],
            self::CONTACT => [
                'contact{1..2}' => [
                    '__construct' => ['@user<current()>'],
                ],
            ],
        ]);

        $this->assertSame(
            $this->loader->getReference('user1'),
            $this->loader->getReference('contact1')->getUser()
        );
        $this->assertSame(
            $this->loader->getReference('user2'),
            $this->loader->getReference('contact2')->getUser()
        );
    }

    /**
     * @group legacy
     */
    public function testCustomSetFunction()
    {
        $loader = $this->createLoader(
            [
                'providers' => [new FakerProvider()]
            ]
        );
        $loader->load(
            [
                self::USER => [
                    'user' => [
                        'username' => 'foo',
                        'fullname' => 'foo bar',
                        '__set' => 'customSetter',
                        'test_variable' => '<noop($username)>',
                    ]
                ]
            ]
        );

        $this->assertEquals('foo set by custom setter', $loader->getReference('user')->username);
        $this->assertEquals('foo bar set by custom setter', $loader->getReference('user')->fullname);
        $this->assertEquals('foo set by custom setter', $loader->getReference('user')->test_variable);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Setter customNonexistantSetter not found in object
     *
     * @group legacy
     */
    public function testCustomNonexistantSetFunction()
    {
        $this->loadData(
            [
                self::USER => [
                    'user' => [
                        'username' => 'foo',
                        'fullname' => 'foo bar',
                        '__set' => 'customNonexistantSetter',
                    ]
                ]
            ]
        );
    }

    public function testUseTypehintInSetter()
    {
        $loader = $this->createLoader();
        $objects = $loader->load([
            Dummy::class => [
               'dummy0' => [
                   'related_dummy' => '@related_dummy0',
               ],
            ],
            RelatedDummy::class => [
                'related_dummy0' => [],
            ],
        ]);

        $dummy0 = $loader->getReference('dummy0');
        $relatedDummy0 = $loader->getReference('related_dummy0');

        $this->assertInstanceOf(Dummy::class, $dummy0);
        $this->assertInstanceOf(RelatedDummy::class, $relatedDummy0);

        $this->assertCount(2, $objects);
        $this->assertSame($dummy0->data, $relatedDummy0);
    }

    /**
     * @group legacy
     */
    public function testUseTypehintInSetterWithLocalFlag()
    {
        $loader = $this->createLoader();
        $objects = $loader->load([
            Dummy::class => [
                'dummy0' => [
                    'related_dummy' => '@related_dummy0',
                ],
            ],
            RelatedDummy::class => [
                'related_dummy0 (local)' => [],
            ],
        ]);

        $dummy0 = $loader->getReference('dummy0');
        $relatedDummy0 = $loader->getReference('related_dummy0');

        $this->assertInstanceOf(Dummy::class, $dummy0);
        $this->assertInstanceOf(RelatedDummy::class, $relatedDummy0);

        $this->assertCount(1, $objects);
        $this->assertSame($dummy0->data, $relatedDummy0);
    }

    public function testUseTypehintInSetterWithInterface()
    {
        $loader = $this->createLoader();
        $objects = $loader->load([
            DummyWithInterface::class => [
                'dummy0' => [
                    'related_dummy' => '@related_dummy0',
                ],
            ],
            RelatedDummy::class => [
                'related_dummy0' => [],
            ],
        ]);

        $dummy0 = $loader->getReference('dummy0');
        $relatedDummy0 = $loader->getReference('related_dummy0');

        $this->assertInstanceOf(DummyWithInterface::class, $dummy0);
        $this->assertInstanceOf(RelatedDummy::class, $relatedDummy0);

        $this->assertCount(2, $objects);
        $this->assertSame($dummy0->data, $relatedDummy0);
    }

    /**
     * @group legacy
     */
    public function testUseTypehintInSetterWithInterfaceAndLocalFlag()
    {
        $loader = $this->createLoader();
        $objects = $loader->load([
            DummyWithInterface::class => [
                'dummy0' => [
                    'related_dummy' => '@related_dummy0',
                ],
            ],
            RelatedDummy::class => [
                'related_dummy0 (local)' => [],
            ],
        ]);

        $dummy0 = $loader->getReference('dummy0');
        $relatedDummy0 = $loader->getReference('related_dummy0');

        $this->assertInstanceOf(DummyWithInterface::class, $dummy0);
        $this->assertInstanceOf(RelatedDummy::class, $relatedDummy0);

        $this->assertCount(1, $objects);
        $this->assertSame($dummy0->data, $relatedDummy0);
    }

    public function testNullVariable()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $loader->load([
            User::class => [
                'user' => [
                    'username' => '<null()>',
                    'fullname' => '<noop($username)>',
                ],
            ],
        ]);

        $this->assertNull($loader->getReference('user')->username);
        $this->assertNull($loader->getReference('user')->fullname);
    }

    public function testAtLiteral()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load([
            self::USER => [
                'foo' => [
                    'username' => 'Bob',
                ],
                'user' => [
                    'username' => 'foo',
                    'friends' => [
                        '\\@<fooGenerator()>',
                        '\\\\@foo',
                        '\\\\\\@foo',
                        '\\foo',
                        '\\\\foo',
                        '\\\\\\foo',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertSame(
            [
                '@foo',
                '\\@foo',
                '\\\\@foo',
                '\\foo',
                '\\\\foo',
                '\\\\\\foo'
            ],
            $res['user']->friends
        );
    }

    public function testAddProcessor()
    {
        $loader = $this->createLoader();
        $loader->addProcessor(new extensions\CustomProcessor);
        $res = $loader->load([
            self::USER => [
                'user' => [
                    'username' => 'uppercase processor:testusername',
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertEquals('TESTUSERNAME', $res['user']->username);
    }

    public function testAddBuilder()
    {
        $loader = $this->createLoader();
        $loader->addBuilder(new extensions\CustomBuilder);
        $res = $loader->load([
            self::USER => [
                'spec dumped' => [
                    'email' => '<email()>',
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['spec dumped']);
        $this->assertNull($res['spec dumped']->email);
    }

    public function testAddInstantiator()
    {
        $loader = $this->createLoader();
        $loader->addInstantiator(new extensions\CustomInstantiator);
        $res = $loader->load([
            self::USER => [
                'user' => [
                    'username' => '<username()>',
                ],
            ],
        ]);

        $this->assertInstanceOf(self::USER, $res['user']);
        $this->assertNotNull($res['user']->uuid);
    }

    public function testAddPopulator()
    {
        $loader = $this->createLoader();
        $loader->addPopulator(new extensions\CustomPopulator);
        $res = $loader->load([
            self::USER => [
                'user' => [
                    'username' => '<username()>',
                ],
            ],
            self::CONTACT => [
                'contact' => [
                    '__construct' => ['@user'],
                    'magicProp' => 'magicValue',
                ],
            ],
        ]);

        $this->assertInstanceOf(self::CONTACT, $res['contact']);
        $this->assertEquals('magicValue set by magic setter', $res['contact']->magicProp);
    }

    public function testCallFakerFromFakerCall()
    {
        $loader = new Loader('en_US', [new FakerProvider]);
        $res = $loader->load(__DIR__.'/../support/fixtures/nested_faker.php');

        foreach (['user1', 'user2'] as $userKey) {
            $user = $res[$userKey];
            $this->assertInstanceOf(self::USER, $user, $userKey.' should match');
            $this->assertEquals('JOHN DOE', $user->username, $userKey.' should match');
            $this->assertEquals('JOHN DOE', $user->fullname, $userKey.' should match');
        }
    }

    public function testSimpleParametersLoading()
    {
        $res = $this->createLoader()->load(__DIR__ . '/Files/parameters/simple.yml');

        $this->assertCount(1, $res);

        $user = $res['alice'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('Alice', $user->username);
    }

    public function testArrayParametersLoading()
    {
        $res = $this->createLoader()->load(__DIR__ . '/Files/parameters/array.yml');

        $this->assertCount(5, $res);
        foreach ($this->loader->getReferences() as $user) {
            $this->assertInstanceOf(self::USER, $user);
            $this->assertContains($user->username, ['Alice', 'Bob', 'Ogi']);
        }
    }

    public function testCompositeParametersLoading()
    {
        $objects = $this->createLoader()->load(__DIR__ . '/Files/parameters/composite.yml');

        $this->assertCount(2, $objects);

        $user = $objects['user0'];
        $this->assertInstanceOf(self::USER, $user);
        //$this->assertEquals('NaN Bat!', $user->username); Not supported yet
        $this->assertEquals('<{key1}> <{key2}>!', $user->username);

        $user = $objects['user1'];
        $this->assertInstanceOf(self::USER, $user);
        //$this->assertEquals('NaN Bat!', $user->username); Not supported yet
        $this->assertEquals('NaN Bat!', $user->username);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Parameter "username_<current()>" was not found.
     */
    public function testDynamicParametersLoading()
    {
        $objects = $this->createLoader()->load(__DIR__ . '/Files/parameters/dynamic.yml');
        $this->fail('Expected exception to be thrown.');

        // Skipped: not supported yet
        //$this->assertCount(2, $objects);

        //$alice = $objects['user_alice'];
        //$this->assertInstanceOf(self::USER, $alice);
        //$this->assertEquals('Alice', $alice->username);

        //$bob = $objects['user_bob'];
        //$this->assertInstanceOf(self::USER, $bob);
        //$this->assertEquals('Bob', $bob->username);
    }

    /**
     * @issue https://github.com/nelmio/alice/issues/664
     */
    public function testParametersShouldBeResolvedOnlyOnce()
    {
        $loader = $this->createLoader();

        $objects = $loader->load(__DIR__.'/../support/fixtures/parameters_664.yml');

        /** @var User $dummy */
        $dummy = $objects['dummy'];
        /** @var User $anotherDummy */
        $anotherDummy = $objects['another_dummy'];

        // This behaviour is not the desired one but is not changed to avoid BC breaks
        $this->assertNotEquals($anotherDummy->username, $dummy->username);
    }

    public function testBackslashes()
    {
        $loader = new Loader();
        $res = $loader->load(__DIR__.'/../support/fixtures/backslashes.yml');

        $this->assertEquals('Bob', $res['foo']->username);

        $this->assertEquals('\\\\', $res['user0']->username);
        $this->assertSame(
            [
                $res['foo'],
                '@foo',
                '\\@foo',
                '\\\\@foo',
            ],
            $res['user0']->friends
        );
    }

    public function testDefaultInstance()
    {
        $res = $this->loadData([
            self::USER => [
                'user (template)' => [
                    'email' => 'base@email.com',
                    'fullname' => 'testfullname'
                ],
                'user2 (extends user)' => null,
            ],
        ]);
        /** @var User $user */
        $user = $res['user2'];

        $this->assertInstanceOf(self::USER, $user);
        $this->assertSame('base@email.com', $user->email);
        $this->assertSame('testfullname', $user->fullname);
    }

    public function testArrayOfNonEntityItems()
    {
        $res = $this->loadData([
            self::USER => [
                'user' => [
                    'username' => '5x <name()>',
                ],
            ],
        ]);
        /** @var User $user */
        $user = $res['user'];

        $this->assertInstanceOf(self::USER, $user);
        // This feature is not supported in 2.x.
        $this->assertFalse(is_array($user->username));
    }

    /**
     * Always return the same structure, see the first sample.
     */
    public function provideSpecialCharactersData()
    {
        $return = [];

        $return['with underscores'] = [
            'data' => [
                self::USER => [
                    'user_alice' => [
                        'username' => 'alice',
                    ],
                    'user_alias' => [
                        'username' => '@user_alice->username',
                    ],
                    'user_deep_alias' => [
                        'username' => '@user_alias->username',
                    ],
                ]
            ],
            'keys' => [
                'user_alice',
                'user_alias',
                'user_deep_alias',
            ],
        ];

        $return['with dots'] = [
            'data' => [
                self::USER => [
                    'user.alice' => [
                        'username' => 'alice',
                    ],
                    'user.alias.alice_alias' => [
                        'username' => '@user.alice->username',
                    ],
                    'user.deep_alias' => [
                        'username' => '@user.alias.alice_alias->username',
                    ],
                ]
            ],
            'keys' => [
                'user.alice',
                'user.alias.alice_alias',
                'user.deep_alias',
            ],
        ];

        $return['with slashes'] = [
            'data' => [
                self::USER => [
                    'user/alice' => [
                        'username' => 'alice',
                    ],
                    'user/alias/alice_alias' => [
                        'username' => '@user/alice->username',
                    ],
                    'user/deep_alias' => [
                        'username' => '@user/alias/alice_alias->username',
                    ],
                ]
            ],
            'keys' => [
                'user/alice',
                'user/alias/alice_alias',
                'user/deep_alias',
            ],
        ];

        return $return;
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

    public function upperCaseProvider($arg)
    {
        return strtoupper($arg);
    }

    public function null()
    {
        return null;
    }
}
