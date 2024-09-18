<?php

namespace App\Http\Controllers\Web\Auth;

use App\Helpers\Auth\RegistersUsers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data): User
    {
        // 检测用户是否被删除
        if (User::whereEmail($data['email'])->onlyTrashed()->exists()) {
            // 还原
            $u = User::whereEmail($data['email'])->onlyTrashed()->first();
            $u->restore();

            $u->name = $data['name'] ?? "User";
            $u->password = Hash::make($data['password']);

            $u->sendEmailVerificationNotification();

            return $u;
        }

        $u = (new User)->create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 发送注册确认邮件
        $u->sendEmailVerificationNotification();
        return $u;
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}
