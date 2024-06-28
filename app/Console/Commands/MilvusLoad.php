<?php

namespace App\Console\Commands;

use App\Support\MilvusSupport;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class MilvusLoad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'milvus:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '加载 Milvus 中的集合。';

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $milvusSupport = new MilvusSupport();

        $milvusSupport->loadCollection(config('milvus.collection'));

        $this->info("已开始加载。");

        return 0;
    }
}
