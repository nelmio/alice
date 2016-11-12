<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice;

use Faker\Factory;
use Nelmio\Alice\scenario2\ImmutableUser;
use Nelmio\Alice\scenario2\MutableUser;
use Nelmio\Alice\scenario2\PublicUser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

require_once __DIR__.'/../../vendor-bin/profiling/vendor/autoload.php';

$fileFinder = new Finder();
$fileFinder->files()->in(__DIR__)->depth(0)->name('*.yml');

$blackfire = new \Blackfire\Client();

$config = new \Blackfire\Profile\Configuration();
$config->setTitle('Scenario 0');
$config->setSamples(10);
$config->setReference(3);

$probe = $blackfire->createProbe($config);

$faker = Factory::create();
$objects = [];

for ($i = 0; $i <= 1000; $i++) {
    $objects['immutable_user_'.$i] = new ImmutableUser(
        $faker->userName,
        $faker->name,
        $faker->dateTime,
        $faker->email,
        $faker->randomNumber
    );
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new MutableUser();
    $user->setUsername($faker->userName);
    $user->setFullname($faker->name);
    $user->setBirthDate($faker->dateTime);
    $user->setEmail($faker->email);
    $user->setFavoriteNumber($faker->randomNumber);

    $objects['mutable_user_'.$i] = $user;
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new PublicUser();
    $user->username = $faker->userName;
    $user->fullname = $faker->name;
    $user->birthDate = $faker->dateTime;
    $user->email = $faker->email;
    $user->favoriteNumber = $faker->randomNumber;

    $objects['public_user_'.$i] = $user;
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new \stdClass();
    $user->username = $faker->userName;
    $user->fullname = $faker->name;
    $user->birthDate = $faker->dateTime;
    $user->email = $faker->email;
    $user->favoriteNumber = $faker->randomNumber;

    $objects['public_user_'.$i] = $user;
}

$blackfire->endProbe($probe);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$output->success('Finished!');
