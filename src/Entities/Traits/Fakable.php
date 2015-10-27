<?php
namespace History\Entities\Traits;

use League\FactoryMuffin\Facade;

trait Fakable
{
    /**
     * Get a fake instance of the model
     *
     * @return static
     */
    public static function fake()
    {
        return Facade::instance(static::class);
    }

    /**
     * Seed a fake instance of the model
     *
     * @return static
     */
    public static function seed()
    {
        return Facade::create(static::class);
    }
}
