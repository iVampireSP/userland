<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification
{
    use Queueable;

    protected $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('团队邀请')
            ->line('您收到一个来自团队 '.$this->team->name.' 的邀请。')
            ->line('邀请人：'.$this->team->owner->name)
            ->action('查看邀请', route('teams.invitations'))
            ->line('如果您不想加入该团队，可以忽略此邮件。');
    }
}
