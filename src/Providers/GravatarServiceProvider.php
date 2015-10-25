<?php
namespace History\Providers;

use League\Container\ServiceProvider;
use thomaswelton\GravatarLib\Gravatar;

class GravatarServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Gravatar::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->singleton(Gravatar::class, function () {
            $gravatar = new Gravatar();
            $gravatar->setDefaultImage('retro');
            $gravatar->setAvatarSize(200);

            return $gravatar;
        });
    }
}
