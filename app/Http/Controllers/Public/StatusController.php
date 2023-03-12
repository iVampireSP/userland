<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;

class StatusController extends Controller
{
    public function __invoke($user)
    {
        // 如果是数字，就是用户 ID
        if (is_numeric($user)) {
            $user = User::find($user);
        }

        // 其余可以用 email 或者  email_md5
        if (! ($user instanceof User)) {
            $user = User::where('email', $user)->orWhere('email_md5', $user)->first();
        }
        // 访问 public const
        return $this->success($user?->status()->first() ?? UserStatus::DEFAULT);
    }
}
