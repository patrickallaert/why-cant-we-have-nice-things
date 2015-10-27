<?php
use History\Entities\Models\User;
use League\FactoryMuffin\Facade as FactoryMuffin;

FactoryMuffin::define(User::class, [
    'name'      => 'userName',
    'full_name' => 'name',
    'email'     => 'email',
]);
