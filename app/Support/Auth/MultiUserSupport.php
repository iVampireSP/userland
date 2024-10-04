<?php

namespace App\Support\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class MultiUserSupport
{
    private string $guard = 'web';

    private string $session_key = 'switch-users';

    private string $route = 'login.select';

    public function setGuard(string $guard): void
    {
        $this->guard = $guard;
    }

    public function count(): int
    {
        return self::get()->count();
    }

    public function add(User $user): bool
    {
        $users = $this->get();
        if ($this->contains($user)) {
            return false;
        }

        $users->push($user);

        Session::put($this->session_key, $users);

        return true;
    }

    public function get(): Collection
    {
        return Session::get($this->session_key, collect());
    }

    public function contains(User $user): bool
    {
        $users = $this->get();

        return $users->contains(function ($u) use ($user) {
            return $u->id === $user->id;
        });
    }

    public function switch(User $user): bool
    {
        if (! $this->contains($user)) {
            return false;
        }

        auth($this->guard)->login($user, true);

        return true;
    }

    public function url(): string
    {
        return route($this->route);
    }

    public function remove(User|Authenticatable|null $user): bool
    {
        if (! $user) {
            return false;
        }

        $users = $this->get()->filter(function ($u) use ($user) {
            return $u->id !== $user->id;
        });

        Session::put($this->session_key, $users);

        return true;
    }

    public function logout(): void
    {
        Session::forget($this->session_key);

        auth($this->guard)->logout();
    }
}
