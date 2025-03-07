<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 通知标题
     *
     * @var string
     */
    protected $title;

    /**
     * 通知内容
     *
     * @var string
     */
    protected $body;

    /**
     * 点击通知后跳转的URL
     *
     * @var string|null
     */
    protected $url;

    /**
     * 通知图标
     *
     * @var string|null
     */
    protected $icon;

    /**
     * 创建一个新的通知实例
     *
     * @param string $title 通知标题
     * @param string $body 通知内容
     * @param string|null $url 点击通知后跳转的URL
     * @param string|null $icon 通知图标
     * @return void
     */
    public function __construct(string $title, string $body, ?string $url = null, ?string $icon = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->icon = $icon;
    }

    /**
     * 获取通知发送的通道
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    /**
     * 获取 WebPush 表示形式的通知
     *
     * @param mixed $notifiable
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable)
    {
        $message = (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->requireInteraction(true);

        if ($this->url) {
            $message->data(['url' => $this->url]);

            // 添加操作按钮，提高 iOS 设备上的用户体验
            $message->action('查看详情', 'view');
        }

        if ($this->icon) {
            $message->icon($this->icon);
        }

        // 设置 TTL (Time To Live) 为 2 小时，确保通知不会过早过期
        // 这对 iOS 设备特别重要，因为它们可能不会立即处理通知
        $message->options(['TTL' => 7200]);

        return $message;
    }
}
