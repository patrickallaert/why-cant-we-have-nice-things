<?php
use History\Application;
use League\Container\Container;

require '../vendor/autoload.php';

$container = new Container();
$app       = new Application($container);

return $app->run();
