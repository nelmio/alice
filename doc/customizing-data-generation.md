# Customizing Data Generation

## Faker Data

Alice integrates with the [Faker](https://github.com/fzaninotto/Faker) library.
Using `<foo()>` you can call Faker data providers to generate random data. Check
the [list of Faker providers](https://github.com/fzaninotto/Faker#formatters).

Let's turn our static bob user into a randomized entry:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: '<username()>'
        fullname: '<firstName()> <lastName()>'
        birthDate: '<date()>'
        email: '<email()>'
        favoriteNumber: '<numberBetween(1, 200)>'
```

As you see in the last line, you can also pass arguments to those just as if
you were calling a function.


### Localized Fake Data

Faker can create localized data for addresses, phone numbers and so on. You can
set the default locale to use by configuring the `locale` value used by Faker
generator. With `NativeLoader`, this can be done by overridding the
`createFakerGenerator()` method. In Symfony, override the
`nelmio_alice.faker.generator` service.

Additionally, you can mix locales by adding a locale prefix to the faker key,
i.e. `<fr_FR:phoneNumber()>` or `<de_DE:firstName()>`.


### Default Providers

### Identity

Alice includes a default identity provider, `<identity()>`, that
simply returns whatever is passed to it. It's content is evaluated so you can
use arithmetic operations for example: `<identity(1 * 2)>`. You can also make
use of the variables and references in it:
`<identity($favoriteNumber * @user1->favoriteNumber)>`.

Some syntactic sugar is provided for this as well, and `<($whatever)>`
is an alias for `<identity($whatever)>`.


## Custom Faker Data Providers

Sometimes you need more than what Faker and Alice provide you natively. For
that, you can register a custom [Faker Provider](https://github.com/fzaninotto/Faker/tree/master/src/Faker/Provider) class:

```php
<?php

namespace App\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class JobProvider extends BaseProvider
{
   /**
    * Sources: {@link http://siliconvalleyjobtitlegenerator.tumblr.com/}
    *
    * @var array List of job titles.
    */
   const TITLE_PROVIDER = [
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
   const ABBREVIATION_PROVIDER = [
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
               self::randomElement(self::TITLE_PROVIDER['firstname']),
               self::randomElement(self::TITLE_PROVIDER['lastname'])
           ),
           self::randomElement(self::TITLE_PROVIDER['fullname']),
       ];

       return self::randomElement($names);
   }

   /**
    * @return string Random job abbreviation title
    */
   public function jobAbbreviation()
   {
       return self::randomElement(self::ABBREVIATION_PROVIDER);
   }
}
```

Then you can add it to the Faker Generator used by Alice by either overridding
the `NativeLoader::createFakerGenerator()` method or the
`nelmio_alice.faker.generator` service if you are using a Dependency Injection
Container.


Previous chapter: [Keep Your Fixtures Dry](fixtures-refactoring.md)<br />
Go back to [Table of Contents](../README.md#table-of-contents)
