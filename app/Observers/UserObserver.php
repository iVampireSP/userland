<?php

namespace App\Observers;

use App\Models\User;
use Faker\Provider\zh_CN\Person;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        if (! $user->name) {
            $user->name = Person::firstNameMale();
        }

        $user->email_md5 = md5($user->email);
        $user->uuid = Str::uuid();
    }

    public function created(User $user): void
    {
        event(new Registered($user));
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('banned_at')) {
            if ($user->banned_at) {
                $user->tokens()->delete();
            }
        }

        if ($user->isDirty('email')) {
            $user->email_md5 = md5($user->email);
        }

        // 如果密码没有被 bcrypt 加密过，就加密
        if ($user->isDirty('password') && ! Str::startsWith($user->password, '$2y$')) {
            $user->password = bcrypt($user->password);
        }

        if ($user->isDirty('id_card') || $user->isDirty('real_name')) {
            if (empty($user->id_card) || empty($user->real_name)) {
                $user->real_name_verified_at = null;
            } else {
                $user->real_name_verified_at = now();
                $user->birthday_at = $user->getBirthdayFromIdCard();
            }
        }

        if (! $user->uuid) {
            $user->uuid = Str::uuid();
        }

        if (! $user->email_md5) {
            $user->email_md5 = md5($user->email);
        }
    }

    public function forceDeleted(User $user): void
    {
        $user->tokens()->delete();
        $user->clients()->delete();
    }
}
