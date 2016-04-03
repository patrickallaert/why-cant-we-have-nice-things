<?php

use History\Entities\Models\Company;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\Threads\Comment;
use History\Entities\Models\Threads\Thread;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade;
use League\FactoryMuffin\Faker\Faker;

/* @var FactoryMuffin $fm */
/** @var Faker $faker */
$faker = Facade::instance();

if (!function_exists('random')) {

    /**
     * @param string $class
     *
     * @return Closure
     */
    function random($class)
    {
        if (!$class::count()) {
            return 'factory|'.$class;
        }

        return function () use ($class) {
            return $class::lists('id')->shuffle()->first();
        };
    }
}

$fm->define(User::class)->setDefinitions([
    'name' => $faker->userName(),
    'full_name' => $faker->name(),
    'email' => $faker->email(),
    'contributions' => $faker->sentence(),
    'company_id' => random(Company::class),
    'no_votes' => $faker->randomNumber(1),
    'yes_votes' => $faker->randomNumber(1),
    'total_votes' => $faker->randomNumber(1),
    'approval' => $faker->randomFloat(null, 0, 1),
    'success' => $faker->randomFloat(null, 0, 1),
    'hivemind' => $faker->randomFloat(null, 0, 1),
    'created_at' => $faker->dateTimeThisYear(),
    'updated_at' => $faker->dateTimeThisYear(),
]);

$fm->define(Request::class)->setDefinitions([
    'name' => $faker->sentence(),
    'contents' => $faker->paragraph(),
    'link' => $faker->url(),
    'condition' => $faker->boolean(2 / 3),
    'approval' => $faker->randomFloat(null, 0, 1),
    'status' => $faker->numberBetween(0, 5),
    'created_at' => $faker->dateTimeThisDecade(),
    'updated_at' => $faker->dateTimeThisDecade(),
])->setCallback(function (Request $request) {
    $users = User::lists('id')->shuffle()->take(2);
    $request->authors()->sync($users->all());
});

$fm->define(Thread::class)->setDefinitions([
    'name' => $faker->sentence(),
    'user_id' => random(User::class),
    'request_id' => random(Request::class),
    'created_at' => $faker->dateTimeThisDecade(),
    'updated_at' => $faker->dateTimeThisDecade(),
]);

$fm->define(Comment::class)->setDefinitions([
    'name' => $faker->sentence(),
    'contents' => $faker->paragraph(),
    'xref' => $faker->randomNumber(1),
    'created_at' => $faker->dateTimeThisYear(),
    'updated_at' => $faker->dateTimeThisYear(),
    'user_id' => random(User::class),
    'thread_id' => random(Thread::class),
]);

$fm->define(Question::class)->setDefinitions([
    'name' => $faker->sentence(),
    'choices' => function () {
        return ['Yes', 'No'];
    },
    'approval' => $faker->randomFloat(null, 0, 1),
    'passed' => $faker->boolean(),
    'request_id' => random(Request::class),
    'created_at' => $faker->dateTimeThisYear(),
    'updated_at' => $faker->dateTimeThisYear(),
]);

$fm->define(Vote::class)->setDefinitions([
    'choice' => $faker->numberBetween(1, 3),
    'question_id' => random(Question::class),
    'user_id' => random(User::class),
    'created_at' => $faker->dateTimeThisYear(),
    'updated_at' => $faker->dateTimeThisYear(),
]);

$fm->define(Company::class)->setDefinitions([
    'name' => $faker->word(),
    'representation' => $faker->randomNumber(1),
]);
