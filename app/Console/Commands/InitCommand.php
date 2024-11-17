<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init {type=web} {--server=roadrunner} {--host=0.0.0.0} {--port=8000} {--queue=default} {--workers=1} {--name=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化应用程序（用于容器启动时）以及启动 Web 服务。 {type} 参数有 web 和 queue 两种，分别用于启动 Web 服务和队列服务。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->call('optimize');

        $type = $this->argument('type');

        if ($type === 'web') {
            $this->info('启动 Web 服务。');
            $this->startWeb();
        } else {
            $this->info('启动队列服务。');
            $this->startQueue();
        }

        return 0;
    }

    public function startWeb(): void
    {
        $workers = $this->option('workers');
        if ($workers == -1) {
            $workers = cpu_count();
        }

        $this->call('octane:start', [
            '--server' => $this->option('server'),
            '--host' => $this->option('host'),
            '--port' => $this->option('port'),
            '--workers' => $workers,
        ]);

        //        system("php -c php.ini server.php start");

    }

    public function startQueue(): void
    {
        $this->call('queue:work', [
            '--queue' => $this->option('queue'),
            '--name' => $this->option('name'),
        ]);
    }
}
