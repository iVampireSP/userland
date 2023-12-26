<?php

namespace App\Logger;

use JetBrains\PhpStorm\NoReturn;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Formatter\LogstashFormatter;

class TestLogger extends AbstractProcessingHandler
{
    public function __invoke(array $config)
    {
//        dd($config);
//        return new Logger(
//            $config['name'] ?? 'defaultChannelName',
//            [
//                new \Monolog\Handler\ElasticsearchHandler(
//                // ...
//                // see phpdoc of the ElasticsearchHandler::class
//                )
//            ]
//        );
    }

    #[NoReturn] protected function write(mixed $record): void
    {
        // log to elasticsearch
    }

}
