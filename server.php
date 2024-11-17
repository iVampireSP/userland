<?php

require_once __DIR__.'/vendor/autoload.php';

use Adapterman\Adapterman;
use Workerman\Worker;

Adapterman::init();

$http_worker = new Worker('http://localhost:8080'); // or 127.0.0.1:8080, or localhost:9000
$http_worker->count = cpu_count(); // or any positive integer
$http_worker->name = env('APP_NAME'); // or any string

$http_worker->onWorkerStart = static function () {
    require __DIR__.'/start.php';
};

$http_worker->onMessage = static function ($connection, $request) {
    $connection->send(run());
};

Worker::runAll();
