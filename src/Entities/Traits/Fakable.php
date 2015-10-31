<?php
namespace History\Entities\Traits;

use League\FactoryMuffin\Facade;

trait Fakable
{
    /**
     * Get a fake instance of the model.
     *
     * @param array $args
     *
     * @return static
     */
    public static function fake(...$args)
    {
        return Facade::instance(static::class, ...$args);
    }

    /**
     * Seed a fake instance of the model.
     *
     * @param integer|array $args
     *
     * @return static
     */
    public static function seed(...$args)
    {
        if ($args && !is_array($args[0])) {
            return Facade::seed($args[0], static::class);
        }

        return Facade::create(static::class, ...$args);
    }
}
