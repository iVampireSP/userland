<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init {--start : 运行 Web 服务}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化应用程序（用于容器启动时）以及启动 Web 服务';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 检查是否已经初始化
        $lock = storage_path('init.lock');
        if (file_exists($lock)) {
            $this->warn("另一个初始化进程正在运行中。如果确定没有其他进程在运行，请手动删除 {$lock} 文件。");
            // 如果有 --start 参数，则启动 Web 服务
            if ($this->option('start')) {
                $this->warn("正在等待另一个进程初始化完成。");
                // 一直等待锁文件被删除
                while (file_exists($lock)) {
                    sleep(1);
                }

                $this->call('serve');
            }

            return;
        }

        $this->info("上锁。");
        // 加锁
        file_put_contents($lock, '');

        // 检测有无 .env
        if (!file_exists(base_path('.env'))) {
            // 复制 .env.example
            $this->info("复制 .env.example 为 .env");
            copy(base_path('.env.example'), base_path('.env'));
        }

        // 检测是否有 APP_KEY
        $APP_KEY = env('APP_KEY');
        if (empty($APP_KEY)) {
            // 初始化
            $this->info("生成应用程序密钥。");
            $this->call('key:generate');
        }

        $this->info("初始化 storage 目录。");
        // 初始化 storage 目录
        $this->initStorageDir();

        $this->info("初始化数据库。");

        // force migrate
        $this->call('migrate', [
            '--force' => true,
        ]);

        $this->info("生成缓存。");
        $this->call('optimize');

        $this->info("解锁");
        // 解锁
        unlink($lock);

        // 输出
        $this->info('应用程序初始化完成。');

        if ($this->option('start')) {
            $this->info('启动 Web 服务。');
            $this->call('octane:start', [
                '--host' => '0.0.0.0'
            ]);
        }
    }

    private function initStorageDir(): void
    {
        // 检测 storage 下的目录是否正确
        $storage = storage_path();

        // 有无 app 目录
        if (!is_dir($storage . '/app')) {
            mkdir($storage . '/app');

            // 有无 public 目录
            if (!is_dir($storage . '/app/public')) {
                mkdir($storage . '/app/public');
            }
        }

        // 有无 framework 目录
        if (!is_dir($storage . '/framework')) {
            mkdir($storage . '/framework');

            // 有无 cache 目录
            if (!is_dir($storage . '/framework/cache')) {
                mkdir($storage . '/framework/cache');
            }

            // 有无 sessions 目录
            if (!is_dir($storage . '/framework/sessions')) {
                mkdir($storage . '/framework/sessions');
            }

            // 有无 testing 目录
            if (!is_dir($storage . '/framework/testing')) {
                mkdir($storage . '/framework/testing');
            }

            // 有无 views 目录
            if (!is_dir($storage . '/framework/views')) {
                mkdir($storage . '/framework/views');
            }
        }

        // 有无 logs 目录
        if (!is_dir($storage . '/logs')) {
            mkdir($storage . '/logs');
        }
    }
}
