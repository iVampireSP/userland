<?php

namespace App\Models;

use App\Exceptions\User\BalanceNotEnoughException;
use App\Helpers\Auth\UserClaimsTrait;
use App\Support\SMS\SMSSupport;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        HasPushSubscriptions,
        HasRoles,
        Notifiable,
        SoftDeletes,
        UserClaimsTrait;

    public array $publics = [
        'id',
        'uuid',
        'name',
        'email',
        'balance',
        'real_name',
        'phone',
        'phone_verified_at',
        'wechat_open_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'current_team_id',
        'uuid',
        'name',
        'email',
        'password',
        'receive_marketing_email',
        'affiliate_id',
        'phone',
        'phone_verified_at',
        'wechat_open_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'real_name',
        'id_card',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'real_name_verified_at' => 'datetime',
        'banned_at' => 'datetime',
        'birthday_at' => 'date:Y-m-d',
        'balance' => 'decimal:4',
    ];

    protected array $guard_name = ['web', 'api'];

    public function isAdult(): bool
    {
        // 如果 birthday_at 为空，那么就返回 false
        return $this->birthday_at?->diffInYears(now()) >= 18;
    }

    public function isRealNamed(): bool
    {
        return ! is_null($this->real_name_verified_at);
    }

    // has phone number
    public function hasPhoneNumber(): bool
    {
        return ! is_null($this->phone);
    }

    // 是否验证手机号
    public function isPhoneVerified(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function isEmailVerified(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function hasWeChatOpenID(): bool
    {
        return ! is_null($this->wechat_open_id);
    }

    public function scopeBirthday(): User
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->select(['id', 'name', 'birthday_at', 'email_md5', 'created_at'])->whereMonth('birthday_at', now()->month)
            ->whereDay('birthday_at', now()->day)->whereNull('banned_at');
    }

    public function status(): HasOne
    {
        return $this->hasOne(UserStatus::class);
    }

    public function getOnlyPublic($appened_excepts = [], $display = []): array
    {
        if ($display) {
            $this->publics = array_merge($this->publics, $display);
        }
        if ($appened_excepts) {
            $this->publics = array_diff($this->publics, $appened_excepts);
        }

        return Arr::only($this->toArray(), $this->publics);
    }

    public function bans(): HasMany
    {
        return $this->hasMany(Ban::class, 'email', 'email');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // has same category package
    public function hasSameCategoryPackage(Package $package): bool
    {
        $package_category_id = $package->category_id;

        // 检测用户当前是否有 $package
        $user_package = $this->packages()->with('package')->where('package_id', $package->id)->first();
        if ($user_package) {
            return $package_category_id == $user_package->package->category_id;
        }

        return false;
    }

    // 给予实名认证权利
    public function giveRealName(): void
    {
        Cache::set('real_name:user:'.$this->id, true, 86400);
    }

    // 是否可以实名认证
    public function hasRealName(): bool
    {
        return Cache::get('real_name:user:'.$this->id, fn () => false);
    }

    public function setTempIdCard(string $name, string $id_card): void
    {
        Cache::set('real_name:user:'.$this->id.':temp_id_card', [
            'name' => $name,
            'id_card' => $id_card,
        ], 86400);
    }

    public function getTempIdCard(): array
    {
        return Cache::get('real_name:user:'.$this->id.':temp_id_card', fn () => [
            'name' => '',
            'id_card' => '',
        ]);
    }

    public function avatar(): string
    {
        return "https://cravatar.cn/avatar/{$this->email_md5}";
    }

    public function faces(): HasMany
    {
        return $this->hasMany(Face::class);
    }

    public function sendSmsVerificationCode(): bool
    {
        $code = rand(1000, 9999);

        $sms = new SMSSupport;

        $sms->setPhone($this->phone);
        $sms->setTemplateId(config('settings.supports.sms.templates.verify_code'));
        $sms->setVariableContent([
            'code' => $code,
        ]);

        try {
            $sms->sendVariable();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        Cache::set('sms:user:code:'.$this->id, $code, config('settings.supports.sms.interval'));

        return true;
    }

    public function getSmsVerificationCode(): string
    {
        return Cache::get('sms:user:code:'.$this->id);
    }

    public function delete(bool $confirm = false): bool
    {
        if ($confirm) {
            parent::delete();
        }

        return $confirm;
    }

    public function createLoginToken(Carbon $expired_at, int $length = 128, string $prefix = 'login', bool $avoid_confusion = false): string
    {
        if ($avoid_confusion) {
            $pool = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
            $token = substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
        } else {
            $token = Str::random($length);
        }

        $sec = $expired_at->diffInSeconds(now());

        if ($sec < 60) {
            $sec = 60;
        }

        // 如果 $prefix 不是以 : 结尾，则增加
        if (! str_ends_with($prefix, ':')) {
            $prefix .= ':';
        }

        Cache::set("token:$prefix".$token, [
            'user_id' => $this->id,
            'expired_at' => $expired_at,
        ], $sec);

        return $token;
    }

    public function getLoginToken(string $token, bool $delete = true, string $prefix = 'login'): ?self
    {
        if (! str_ends_with($prefix, ':')) {
            $prefix .= ':';
        }

        $data = Cache::get("token:$prefix".$token);

        if (! $data) {
            return null;
        }

        $user_id = $data['user_id'];

        $expired_at = $data['expired_at'];

        if ($delete) {
            Cache::forget("token:$prefix".$token);
        }

        // 是否过期
        if ($expired_at->isPast()) {
            return null;
        }

        return self::find($user_id);
    }

    public function findForPassport(string $username): self
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return $this->where('email', $username)->first();
        }

        if (filter_var($username, FILTER_VALIDATE_INT)) {
            // 根据 id 或 手机号
            return $this->where('id', $username)->orWhere('phone', $username)->first();
        }

        // 默认 ID
        return $this->where('id', $username)->first();
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function getCurrentTeam(): array
    {
        $this->load('currentTeam');

        if ($this->currentTeam) {
            $teamUser = \DB::table('team_users')
                ->where('team_id', $this->currentTeam->id)
                ->where('user_id', $this->id)
                ->first();

            $role = $teamUser ? $teamUser->role : null;
            $isTeamOwner = ($this->currentTeam->owner_id === $this->id);
        } else {
            $role = null;
            $isTeamOwner = false;
        }

        return [
            'current_team' => $this->currentTeam,
            'current_team_role' => $role,
            'is_current_team_owner' => $isTeamOwner,
        ];
    }

    public function hasBalance(string $amount = '0.01'): bool
    {
        return bccomp($this->balance, $amount, 4) >= 0;
    }

    /**
     * 扣除费用
     */
    public function reduce(?string $amount = '0', string $description = '消费', bool $fail = false)
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        /**
         * @throws BalanceNotEnoughException
         */
        return Cache::lock('user_balance_'.$this->id, 10)->block(10, function () use ($amount) {
            $this->refresh();
            //
            //            if ($this->balance < $amount) {
            // //                if ($fail) {
            //                    // 发送邮件通知
            // //                    $this->notify(new BalanceNotEnough());
            //
            // //                    throw new BalanceNotEnoughException();
            // //                }
            //            }

            $this->balance = bcsub($this->balance, $amount, 4);
            $this->save();

            // 如果用户的余额小于 5 元，则发送邮件提醒（一天只发送一次，使用缓存）
            //            if (! $this->hasBalance(5) && ! Cache::has('user_balance_less_than_5_'.$this->id)) {
            //                $this->notify(new LowBalance());
            //                Cache::put('user_balance_less_than_5_'.$this->id, true, now()->addDay());
            //            }

        });
    }

    /**
     * 增加余额
     */
    public function charge(?string $amount = '0', string $payment = 'console', string $description = '充值')
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        return Cache::lock('user_balance_'.$this->id, 10)->block(10, function () use ($amount, $description, $payment) {
            $this->refresh();
            $this->balance = bcadd($this->balance, $amount, 4);
            $this->save();

            (new Balance)->create([
                'user_id' => $this->id,
                'amount' => $amount,
                'payment' => $payment,
                'description' => $description,
                'paid_at' => now(),
            ]);

            // if (isset($options['add_balances_log']) && $options['add_balances_log'] === true) {
            //     (new Balance)->create([
            //         'user_id' => $this->id,
            //         'amount' => $amount,
            //         'payment' => $payment,
            //         'description' => $description,
            //         'paid_at' => now(),
            //     ]);
            // }

        });
    }
}
