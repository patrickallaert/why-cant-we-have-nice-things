<?php

use History\Entities\Models\Comment;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use League\FactoryMuffin\Facade as FactoryMuffin;

if (!function_exists('random')) {
    /*
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

FactoryMuffin::define(User::class, [
    'name'       => 'userName',
    'full_name'  => 'name',
    'email'      => 'email',
    'created_at' => 'dateTimeThisYear',
    'updated_at' => 'dateTimeThisYear',
]);

FactoryMuffin::define(Request::class, [
    'name'       => 'sentence',
    'contents'   => 'paragraph',
    'link'       => 'url',
    'condition'  => '2/3',
    'created_at' => 'dateTimeThisDecade',
    'updated_at' => 'dateTimeThisDecade',
], function (Request $request) {
    $users = User::lists('id')->shuffle()->take(2);
    $request->authors()->sync($users->all());
});

FactoryMuffin::define(Comment::class, [
    'name'       => 'sentence',
    'contents'   => 'paragraph',
    'xref'       => 'number',
    'created_at' => 'dateTimeThisYear',
    'updated_at' => 'dateTimeThisYear',
    'user_id'    => random(User::class),
    'request_id' => random(Request::class),
]);

FactoryMuffin::define(Question::class, [
    'name'       => 'sentence',
    'choices'    => 'numberBetween|1;5',
    'request_id' => random(Request::class),
    'created_at' => 'dateTimeThisYear',
    'updated_at' => 'dateTimeThisYear',
]);

FactoryMuffin::define(Vote::class, [
    'choice'      => 'numberBetween|1;3',
    'question_id' => random(Question::class),
    'user_id'     => random(User::class),
    'created_at'  => 'dateTimeThisYear',
    'updated_at'  => 'dateTimeThisYear',
]);
