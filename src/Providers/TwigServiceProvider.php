<?php
namespace History\Providers;

use History\Application;
use History\Entities\Models\Question;
use History\Entities\Models\Vote;
use League\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;

class TwigServiceProvider extends ServiceProvider
{
    /**
     * @var int
     */
    const PRECISION = 1;

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
            $debug = getenv('APP_ENV') === 'local';
            $twig = new Twig_Environment($loader, [
                'debug'            => $debug,
                'auto_reload'      => $debug,
                'strict_variables' => false,
                'cache'            => $this->container->get('paths.cache'),
            ]);

            // Configure Twig
            $this->registerGlobalVariables($twig);
            $this->addTwigExtensions($twig);

            return $twig;
        });
    }

    /**
     * Add extensions to Twig.
     *
     * @param Twig_Environment $twig
     */
    private function addTwigExtensions(Twig_Environment $twig)
    {
        $twig->addExtension(new Twig_Extension_Debug());

        $twig->addFunction(new Twig_SimpleFunction('percentage', function ($number) {
            return round($number * 100, self::PRECISION);
        }));

        $twig->addFunction(new Twig_SimpleFunction('choice', function (Question $question, Vote $vote) {
            if ($question->choices <= 2) {
                return $vote->choice === 1 ? 'Yes' : 'No';
            }

            return $vote->choice;
        }));
    }

    /**
     * Register global variables with Twig.
     *
     * @param Twig_Environment $twig
     */
    private function registerGlobalVariables(Twig_Environment $twig)
    {
        $twig->addGlobal('app_name', Application::NAME);

        $request = $this->container->get(Request::class);
        $twig->addGlobal('current_uri', $request->getPathInfo());
        $twig->addGlobal('precision', self::PRECISION);
        $twig->addGlobal('assets', $this->getWebpackAssets());

        $twig->addGlobal('navigation', [
            ['uri' => '/users', 'label' => 'Users'],
            ['uri' => '/votes', 'label' => 'Votes'],
            ['uri' => '/requests', 'label' => 'RFCs'],
            ['uri' => '/about', 'label' => 'About'],
        ]);
    }

    /**
     * Bind the path to the Webpack assets to the views.
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
