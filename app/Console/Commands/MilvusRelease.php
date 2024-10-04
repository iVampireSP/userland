<?php

namespace App\Console\Commands;

use App\Support\Milvus\MilvusSupport;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class MilvusRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'milvus:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '释放 Milvus 中的集合。';

    /**
     * Execute the console command.
     *
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $milvusSupport = new MilvusSupport;

        $result = $milvusSupport->releaseCollection(config('milvus.collection'));

        $this->warn('已开始释放。');

        return 0;
    }
}
