<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));



if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}


require __DIR__.'/../vendor/autoload.php';


$app = require_once __DIR__.'/bootstrap/app.php';

global $kernel;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);


function run()
{
    global $kernel;

    ob_start();

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);

    return ob_get_clean();
}
