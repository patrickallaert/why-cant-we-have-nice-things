<?php
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use League\FactoryMuffin\Facade as FactoryMuffin;

FactoryMuffin::define(User::class, [
    'name'      => 'userName',
    'full_name' => 'name',
    'email'     => 'email',
]);

FactoryMuffin::define(Request::class, [
    'name'      => 'sentence',
    'contents'  => 'paragraph',
    'link'      => 'url',
    'condition' => '2/3',
]);

FactoryMuffin::define(Question::class, [
    'name'       => 'sentence',
    'choices'    => 'numberBetween:1,5',
    'request_id' => 'factory|'.Request::class,
]);

FactoryMuffin::define(Vote::class, [
    'choice'      => 'numberBetween:1,3',
    'question_id' => 'factory|'.Question::class,
    'user_id'     => 'factory|'.User::class,
]);
