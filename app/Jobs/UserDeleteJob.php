<?php

namespace App\Jobs;

use App\Mail\UserDeleted;
use App\Models\Client;
use App\Models\Face;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ?User $user
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (is_null($this->user)) {
            return;
        }

        // 删除所有 client
        Client::whereUserId($this->user->id)->chunk(100, function ($clients) {
            $clients->each(function ($client) {
                $client->delete();
            });
        });

        // Token
        $this->user->tokens()->delete();

        // 删除所有 Face
        Face::whereUserId($this->user->id)->chunk(100, function ($faces) {
            $faces->each(function ($face) {
                $face->delete();
            });
        });

        if ($this->user->email) {
            Mail::to($this->user->email)->send(new UserDeleted);
        }

        $r = $this->user->delete(true);

        Log::info('队列删除用户', [
            'user' => $this->user->toArray(),
            'result' => $r,
        ]);
    }
}
