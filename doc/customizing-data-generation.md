# Customizing Data Generation

## Faker Data

Alice integrates with the [Faker](https://github.com/fzaninotto/Faker) library.
Using `<foo()>` you can call Faker data providers to generate random data. Check
the [list of Faker providers](https://github.com/fzaninotto/Faker#formatters).

Let's turn our static bob user into a randomized entry:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: <username()>
        fullname: <firstName()> <lastName()>
        birthDate: <date()>
        email: <email()>
        favoriteNumber: <numberBetween(1, 200)>
```

As you see in the last line, you can also pass arguments to those just as if
you were calling a function.

To pass Faker Data to another Faker provider, you can use the `$fake()` closure
within faker calls. For example use `$fake('firstName', 'de_DE')` or
`$fake('numberBetween', null, 1, 200)` to call Faker. Pass the provider to call
followed by the locale (or null) and then the arguments to the provider. Here
is a detailed yaml example.

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: User<identity($fake('numberBetween', 1, 100) / 2 + 5)>
```

In plain PHP fixtures the `$fake` closure is also available.


### Localized Fake Data

Faker can create localized data for addresses, phone numbers and so on. You can
set the default locale to use by passing a `locale` value in the `$options`
array of `Fixtures::load`.

Additionally, you can mix locales by adding a locale prefix to the faker key,
i.e. `<fr_FR:phoneNumber()>` or `<de_DE:firstName()>`.

### Default Providers

Alice includes a default identity provider, `<identity()>`, that
simply returns whatever is passed to it. This allows you among other
things to use a PHP expression while still benefitting from
[variable replacement](#variables). This is similar to an `eval()`
call, allowing you to do things like math or similar, e.g.
`<identity(1 + $favoriteNumber)>`.

Some syntactic sugar is provided for this as well, and `<($whatever)>`
is an alias for `<identity($whatever)>`.


## Reuse generated data using objects value

Sometimes you require value objects that are not persisted by an ORM, but
are just stored on other objects. You can use the `(local)` flag on the class
or the instance name to mark them as non-persistable. They will be available
as references to use in other objects, but will not be returned by the
`LoaderInterface::load` call.

For example this avoids getting an error because Geopoint is not an Entity
if you use the Doctrine persister.

```yaml
Nelmio\Data\Geopoint (local):
    geo1:
        __construct: [<latitude()>, <longitude()>]

Nelmio\Entity\Location:
    loc{1..100}:
        name: <city()>
        geopoint: '@geo1'
```


## Custom Faker Data Providers

Sometimes you need more than what Faker and Alice provide you natively, and
there are three ways to solve the problem:

#### Embed PHP code in the yaml file

It is included by the loader so you can add arbitrary PHP as long as it outputs
valid yaml. That said, this is like PHP templates, it quickly ends up very messy
if you do too much logic, so it's best to extract logic out of the templates.
  
#### Public method in the Loader

All the public methods are available as `<method()>` in the Alice fixture files.
For example if you want a custom group name generator and you use the standard
Doctrine Fixtures package in a Symfony2 project, you could do the following:

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Nelmio\Alice\Fixtures;

class LoadFixtureData implements FixtureInterface
{
   public function load(ObjectManager $manager)
   {
       // pass $this as an additional faker provider to make the "groupName"
       // method available as a data provider
       Fixtures::load(__DIR__.'/fixtures.yml', $manager, ['providers' => [$this]]);
   }

   public function groupName()
   {
       $names = [
           'Group A',
           'Group B',
           'Group C',
       ];

       return $names[array_rand($names)];
   }
}
```

That way you can now use `name: <groupName()>` to generate specific group names.
   
#### Add a custom [Faker Provider](https://github.com/fzaninotto/Faker/tree/master/src/Faker/Provider) class

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use Faker\Provider\Base as BaseProvider;

class JobProvider extends BaseProvider
{
   /**
    * Sources: {@link http://siliconvalleyjobtitlegenerator.tumblr.com/}
    *
    * @var array List of job titles.
    */
   private $titleProvider = [
       'firstname' => [
           'Audience Recognition',
           'Big Data',
           'Bitcoin',
           '...',
           'Video Experience',
           'Wearables',
           'Webinar',
       ],
       'lastname' => [
           'Advocate',
           'Amplifier',
           'Architect',
           '...',
           'Warlock',
           'Watchman',
           'Wizard',
       ],
       'fullname' => [
           'Conductor of Datafication',
           'Crowd-Funder-in-Residence',
           'Quantified-Self-in-Residence',
           '...',
           'Tech-Svengali-in-Residence',
           'Tech-Wizard-in-Residence',
           'Thought-Leader-in-Residence',
       ],
   ];

   /**
    * Sources: {@link http://sos.uhrs.indiana.edu/Job_Code_Title_Abbreviation_List.htm}
    *
    * @var array List of job abbreviations.
    */
   private $abbreviationProvider = [
       'ABATE',
       'ACAD',
       'ACCT',
       '...',
       'WCTR',
       'WSTRN',
       'WKR',
   ];


   /**
    * @return string Random job title.
    */
   public function jobTitle()
   {
       $names = [
           sprintf(
               '%s %s',
               self::randomElement($this->titleProvider['firstname']),
               self::randomElement($this->titleProvider['lastname'])
           ),
           self::randomElement($this->titleProvider['fullname']),
       ];
       return self::randomElement($names);
   }
   /**
    * @return string Random job abbreviation title
    */
   public function jobAbbreviation()
   {
       return self::randomElement($this->abbreviationProvider);
   }
}
```

You will need to inject a Faker generator instance, which you can get thanks to [`Nelmio\Alice\Instances\Processor\Methods\Faker`](../src/Nelmio/Alice/Instances/Processor/Methods/Faker.php).

Then, inject your provider to the [`Nelmio\Alice\Fixtures\Loader`](../src/Nelmio/Alice/Fixtures/Loader.php) or when calling [`Nelmio\Alice\Fixtures::load()`](../src/Nelmio/Alice/Fixtures.php#L55).

Next chapter: [Event handling with Processors](processors.md)<br />
Previous chapter: [Keep Your Fixtures Dry](fixtures-refactoring.md)
