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
    public static function fake(...$args)
    {
        return Facade::instance(static::class, ...$args);
    }

    /**
     * Seed a fake instance of the model
     *
     * @return static
     */
    public static function seed(...$args)
    {
        return Facade::create(static::class, ...$args);
    }
}
