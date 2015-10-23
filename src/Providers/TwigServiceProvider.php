<?php
namespace History\Providers;

use League\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

class TwigServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Twig_Environment::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->singleton(Twig_Environment::class, function () {
            $loader = new Twig_Loader_Filesystem($this->container->get('paths.views'));
            $twig   = new Twig_Environment($loader, [
                'auto_reload'      => getenv('APP_ENV') === 'local',
                'strict_variables' => false,
                'cache'            => $this->container->get('paths.cache'),
            ]);

            // Configure Twig
            $this->registerGlobalVariables($twig);
            $twig->addExtension(new Twig_Extension_Debug());

            return $twig;
        });
    }

    /**
     * Register global variables with Twig
     *
     * @param Twig_Environment $twig
     */
    private function registerGlobalVariables(Twig_Environment $twig)
    {
        $request = $this->container->get(Request::class);
        $twig->addGlobal('current_uri', $request->getPathInfo());
        $twig->addGlobal('precision', 1);
        $twig->addGlobal('assets', $this->getWebpackAssets());

        $twig->addGlobal('navigation', [
            ['uri' => '/users', 'label' => 'Users'],
            ['uri' => '/requests', 'label' => 'RFCs'],
            ['uri' => '/about', 'label' => 'About'],
        ]);
    }

    /**
     * Bind the path to the Webpack assets to the views
     *
     * @return array
     */
    private function getWebpackAssets()
    {
        $assets = $this->container->get('paths.builds').'/manifest.json';
        $assets = file_get_contents($assets);
        $assets = json_decode($assets, true);

        return $assets;
    }
}
