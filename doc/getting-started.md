# Getting Started

1. [Basic Usage](#basic-usage)
1. [Framework integration](#framework-integration)
    1. [Symfony](#symfony)


## Basic Usage

The easiest way to use this is to call the `Nelmio\Alice\Loader\NativeLoader`
loader. It is ready to use and does not require any framework integration. It
allows you to load any file or an array of data.

```yaml
# Example of YAML file

Nelmio\Entity\User:
    user{1..10}:
        username: '<username()>'
        fullname: '<firstName()> <lastName()>'
        birthDate: '<date_create()>'
        email: '<email()>'
        favoriteNumber: '50%? <numberBetween(1, 200)>'

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
        members: '<numberBetween(1, 10)>x @user*'
        created: '<dateTimeBetween("-200 days", "now")>'
        updated: '<dateTimeBetween($created, "now")>'
```

```php
<?php
// Example of PHP file

return [
    \Nelmio\Entity\User::class => [
        'user{1..10}' => [
            'username' => '<username()>',
            'fullname' => '<firstName()> <lastName()>',
            'birthDate' => '<date_create()>',
            'email' => '<email()>',
            'favoriteNumber' => '50%? <numberBetween(1, 200)>',
        ],
    ],
    \Nelmio\Entity\Group::class => [
        'group1' => [
            'name' => 'Admins',
            'owner' => '@user1',
            'members' => '<numberBetween(1, 10)>x @user*',
            'created' => '<dateTimeBetween("-200 days", "now")>',
            'updated' => '<dateTimeBetween($created, "now")>',
        ],
    ],
];
```

```php
$loader = new Nelmio\Alice\Loader\NativeLoader();
$objectSet = $loader->loadFile(__DIR__.'/fixtures.yml');
// or
$objectSet = $loader->loadFile(__DIR__.'/fixtures.php');
```

```php
$loader = new Nelmio\Alice\Loader\NativeLoader();
$objectSet = $loader->loadData([
    \Nelmio\Entity\User::class => [
        'user{1..10}' => [
            'username' => '<username()>',
            'fullname' => '<firstName()> <lastName()>',
            'birthDate' => '<date_create()>',
            'email' => '<email()>',
            'favoriteNumber' => '50%? <numberBetween(1, 200)>',
        ],
    ],
    \Nelmio\Entity\Group::class => [
        'group1' => [
            'name' => 'Admins',
            'owner' => '@user1',
            'members' => '<numberBetween(1, 10)>x @user*',
            'created' => '<dateTimeBetween("-200 days", "now")>',
            'updated' => '<dateTimeBetween($created, "now")>',
        ],
    ],
]);
```

When loading a file or an array of data, you can inject parameters and objects:

```php
$loader = new Nelmio\Alice\Loader\NativeLoader();
$objectSet = $loader->loadData(
    [
        \Nelmio\Entity\Group::class => [
            'group1' => [
                'name' => '<{name}>',
                'owner' => '@user1',
            ],
        ],
    ],
    ['name' => 'Admins'],
    ['user1' => $user1]
);
```

This, among other things, allows you to load several files successively even if
they are dependent (you can also make use of the
[include directive](fixtures-refactoring.md#including-files)):

```php
$loader = new Nelmio\Alice\Loader\NativeLoader();

$objectSet = $loader->loadFile(__DIR__.'/users.yml');
$objectSet = $loader->loadFile(
    __DIR__.'/groups.yml',
    $objectSet->getParameters(),
    $objectSet->getObjects()
);
```

## Framework integration

### Symfony

Alice comes with a Symfony Bundle
[`NelmioAliceBundle`](/src/Bridge/Symfony/NelmioAliceBundle.php). To enabled it,
update your application kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    //...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
    }

    return $bundles;
}
```

You can then configure the bundle to your needs:

```yaml
# app/config/config_dev.yml

nelmio_alice:
    locale: 'en_US' # Default locale for the Faker Generator
    seed: 1 # Value used make sure Faker generates data consistently across
            # runs, set to null to disable.
    functions_blacklist: # Some Faker formatter may have the same name as PHP
        - 'current'      # native functions. PHP functions have the priority,
                         # so if you want to use a Faker formatter instead,
                         # blacklist this function here
    loading_limit: 5 # Alice may do some recursion to resolve certain values.
                     # This parameter defines a limit which will stop the
                     # resolution once reached.
    max_unique_values_retry: 150 # Maximum number of time Alice can try to
                                   # generate a unique value before stopping and
                                   # failing.
```

Note: When using `<current()>` with the Alice built-in provider, be sure `current`
is in the `functions_blacklist` if you append more functions.

<br />
<hr />

« [Complete Reference](complete-reference.md) • [Installation](../README.md#installation) »
