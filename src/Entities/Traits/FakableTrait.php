<?php

namespace History\Entities\Traits;

use League\FactoryMuffin\FactoryMuffin;

trait FakableTrait
{
    /**
     * Get a fake instance of the model.
     *
     * @param array ...$args
     *
     * @return static
     */
    public static function fake(...$args)
    {
        return static::getFactoryMuffin()->instance(static::class, ...$args);
    }

    /**
     * @throws \League\FactoryMuffin\Exceptions\DirectoryNotFoundException
     *
     * @return FactoryMuffin
     */
    protected static function getFactoryMuffin()
    {
        $muffin = new FactoryMuffin();
        $muffin->loadFactories(realpath(__DIR__.'/../../../resources/factories'));

        return $muffin;
    }

    /**
     * Seed a fake instance of the model.
     *
     * @param int|array ...$args
     *
     * @return static
     */
    public static function seed(...$args)
    {
        if ($args && !is_array($args[0])) {
            return static::getFactoryMuffin()->seed($args[0], static::class);
        }

        return static::getFactoryMuffin()->create(static::class, ...$args);
    }
}
