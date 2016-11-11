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
        $faker->unique()->userName,
        $faker->unique()->name,
        $faker->unique()->dateTime,
        $faker->unique()->email,
        $faker->unique()->randomNumber
    );
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new MutableUser();
    $user->setUsername($faker->unique()->userName);
    $user->setFullname($faker->unique()->name);
    $user->setBirthDate($faker->unique()->dateTime);
    $user->setEmail($faker->unique()->email);
    $user->setFavoriteNumber($faker->unique()->randomNumber);

    $objects['mutable_user_'.$i] = $user;
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new PublicUser();
    $user->username = $faker->unique()->userName;
    $user->fullname = $faker->unique()->name;
    $user->birthDate = $faker->unique()->dateTime;
    $user->email = $faker->unique()->email;
    $user->favoriteNumber = $faker->unique()->randomNumber;

    $objects['public_user_'.$i] = $user;
}
for ($i = 0; $i <= 1000; $i++) {
    $user = new \stdClass();
    $user->username = $faker->unique()->userName;
    $user->fullname = $faker->unique()->name;
    $user->birthDate = $faker->unique()->dateTime;
    $user->email = $faker->unique()->email;
    $user->favoriteNumber = $faker->unique()->randomNumber;

    $objects['public_user_'.$i] = $user;
}

$blackfire->endProbe($probe);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$output->success('Finished!');
