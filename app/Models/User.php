<?php

namespace App\Models;

use App\Support\SMSSupport;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;

// use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public array $publics = [
        'id',
        'uuid',
        'name',
        'email',
        'real_name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'receive_marketing_email',
        'affiliate_id',
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
    ];

    public function isAdult(): bool
    {
        // 如果 birthday_at 为空，那么就返回 false
        return $this->birthday_at?->diffInYears(now()) >= 18;
    }

    public function isRealNamed(): bool
    {
        return $this->real_name_verified_at !== null;
    }

    // has phone number
    public function hasPhoneNumber(): bool
    {
        return $this->phone !== null;
    }

    // 是否验证手机号
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
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

        $sms = new SMSSupport();

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
}
